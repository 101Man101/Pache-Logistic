<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\FinancialLedger;
use App\Models\Shipment;
use App\Models\Tariff;
use App\Models\TrackingLog;
use App\Modules\OMS\Domain\Exceptions\CreditLimitExceededException;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\Shared\Domain\ValueObjects\AwbNumber;
use App\Modules\Shared\Domain\ValueObjects\Dimensions;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class BookShipmentUseCase
{
    /**
     * @param  array{
     *     customer_id: int,
     *     origin_branch_id: int,
     *     destination_branch_id: int,
     *     reference_no?: string|null,
     *     shipper_name: string,
     *     shipper_phone: string,
     *     shipper_address: string,
     *     consignee_name: string,
     *     consignee_phone: string,
     *     consignee_address: string,
     *     service_type: string,
     *     payment_method: string,
     *     declared_value?: float,
     *     cash_received?: bool,
     *     items: list<array{description: string, qty: int, actual_weight_kg: float, length_cm: float, width_cm: float, height_cm: float}>
     * }  $payload
     */
    public function execute(array $payload, ?int $actorId = null): Shipment
    {
        $paymentMethod = PaymentMethod::from($payload['payment_method']);

        return DB::transaction(function () use ($payload, $paymentMethod, $actorId): Shipment {
            /** @var Customer $customer */
            $customer = Customer::query()->lockForUpdate()->findOrFail($payload['customer_id']);
            /** @var Branch $originBranch */
            $originBranch = Branch::query()->findOrFail($payload['origin_branch_id']);
            /** @var Branch $destBranch */
            $destBranch = Branch::query()->findOrFail($payload['destination_branch_id']);

            if ($paymentMethod === PaymentMethod::TOP && ! $customer->is_top_enabled) {
                throw new DomainException("Pelanggan {$customer->name} belum memiliki kontrak aktif untuk metode B2B TOP.");
            }

            /** @var Tariff|null $tariff */
            $tariff = Tariff::query()
                ->where('origin_branch_id', $originBranch->id)
                ->where('destination_branch_id', $destBranch->id)
                ->where('service_type', $payload['service_type'])
                ->where('is_active', true)
                ->first();

            $divisor = $tariff !== null
                ? $tariff->volumetric_divisor
                : match ($payload['service_type']) {
                    'EXP' => 5000,
                    'CRG' => 4000,
                    default => 6000,
                };
            $ratePerKg = $tariff !== null ? (float) $tariff->rate_per_kg : 14000.0;
            $minWeightKg = $tariff !== null ? (float) $tariff->min_weight_kg : 1.0;
            $insurancePct = $tariff !== null ? (float) $tariff->insurance_rate_pct : 0.25;

            $totalActual = 0.0;
            $totalVolumetric = 0.0;

            foreach ($payload['items'] as $item) {
                $qty = max(1, (int) $item['qty']);
                $dim = new Dimensions(
                    lengthCm: (float) $item['length_cm'],
                    widthCm: (float) $item['width_cm'],
                    heightCm: (float) $item['height_cm'],
                );
                $totalActual += ((float) $item['actual_weight_kg']) * $qty;
                $totalVolumetric += $dim->volumetricWeightKg($divisor) * $qty;
            }

            $chargeableWeight = max($minWeightKg, (float) ceil(max($totalActual, $totalVolumetric)));
            $shippingCost = round($chargeableWeight * $ratePerKg, 2);
            $declaredValue = max(0.0, (float) ($payload['declared_value'] ?? 0.0));
            $insuranceCost = $declaredValue > 0 ? round($declaredValue * ($insurancePct / 100), 2) : 0.0;
            $totalAmount = round($shippingCost + $insuranceCost, 2);

            // BR-02: Credit Limit Enforcement for TOP customers
            if ($paymentMethod === PaymentMethod::TOP) {
                $exposure = $customer->currentCreditExposure() + $totalAmount;
                $creditLimit = (float) $customer->credit_limit;
                if ($exposure > $creditLimit) {
                    throw CreditLimitExceededException::forCustomer($customer->name, $exposure, $creditLimit);
                }
            }

            $dateYmd = now()->format('ymd');
            $todayPrefix = 'PCH-'.strtoupper(substr($originBranch->code, 0, 3))."-{$dateYmd}-";
            $lastAwb = Shipment::query()
                ->where('awb_number', 'like', "{$todayPrefix}%")
                ->orderByDesc('awb_number')
                ->lockForUpdate()
                ->value('awb_number');
            $nextSeq = is_string($lastAwb) ? ((int) substr($lastAwb, -5) + 1) : 1;
            $awb = AwbNumber::generate($originBranch->code, $dateYmd, $nextSeq);

            $isCashPaidNow = $paymentMethod === PaymentMethod::CASH && (bool) ($payload['cash_received'] ?? false);

            $paymentStatus = match (true) {
                $paymentMethod === PaymentMethod::TOP => PaymentStatus::BILLED,
                $isCashPaidNow => PaymentStatus::PAID,
                default => PaymentStatus::PENDING,
            };

            $paymentReference = match ($paymentMethod) {
                PaymentMethod::ONLINE => 'VA-PCH-'.strtoupper(substr(md5($awb->value), 0, 10)),
                PaymentMethod::CASH => $isCashPaidNow ? 'RCPT-'.$awb->value : null,
                PaymentMethod::TOP => 'TOP-NET'.$customer->top_days.'-'.$customer->code,
            };

            /** @var Shipment $shipment */
            $shipment = Shipment::query()->create([
                'awb_number' => $awb->value,
                'reference_no' => $payload['reference_no'] ?? null,
                'customer_id' => $customer->id,
                'origin_branch_id' => $originBranch->id,
                'destination_branch_id' => $destBranch->id,
                'shipper_name' => $payload['shipper_name'],
                'shipper_phone' => $payload['shipper_phone'],
                'shipper_address' => $payload['shipper_address'],
                'consignee_name' => $payload['consignee_name'],
                'consignee_phone' => $payload['consignee_phone'],
                'consignee_address' => $payload['consignee_address'],
                'service_type' => $payload['service_type'],
                'volumetric_divisor' => $divisor,
                'actual_weight_kg' => round($totalActual, 3),
                'volumetric_weight_kg' => round($totalVolumetric, 3),
                'chargeable_weight_kg' => round($chargeableWeight, 3),
                'shipping_cost' => $shippingCost,
                'insurance_cost' => $insuranceCost,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_reference' => $paymentReference,
                'paid_at' => $paymentStatus === PaymentStatus::PAID ? now() : null,
                'status' => ShipmentStatus::BOOKED,
                'created_by' => $actorId,
            ]);

            foreach ($payload['items'] as $item) {
                $shipment->items()->create([
                    'description' => $item['description'],
                    'qty' => max(1, (int) $item['qty']),
                    'actual_weight_kg' => $item['actual_weight_kg'],
                    'length_cm' => $item['length_cm'],
                    'width_cm' => $item['width_cm'],
                    'height_cm' => $item['height_cm'],
                ]);
            }

            TrackingLog::query()->create([
                'shipment_id' => $shipment->id,
                'branch_id' => $originBranch->id,
                'status' => ShipmentStatus::BOOKED->value,
                'location' => "{$originBranch->name} ({$originBranch->city})",
                'description' => "Resi diterbitkan (Non-COD · {$paymentMethod->label()} · Status Bayar: {$paymentStatus->label()}).",
                'latitude' => $originBranch->latitude,
                'longitude' => $originBranch->longitude,
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            if ($paymentStatus === PaymentStatus::PAID) {
                FinancialLedger::query()->create([
                    'entry_code' => 'FIN-'.now()->format('ymdHis').'-'.str_pad((string) $shipment->id, 4, '0', STR_PAD_LEFT),
                    'branch_id' => $originBranch->id,
                    'channel' => $paymentMethod->value,
                    'direction' => 'credit',
                    'amount' => $totalAmount,
                    'reference_type' => 'shipment',
                    'reference_id' => $shipment->id,
                    'description' => "Penerimaan tunai di muka loket asal untuk resi {$shipment->awb_number}",
                    'actor_id' => $actorId,
                    'created_at' => now(),
                ]);
            }

            return $shipment->load(['items', 'originBranch', 'destinationBranch', 'customer']);
        });
    }
}
