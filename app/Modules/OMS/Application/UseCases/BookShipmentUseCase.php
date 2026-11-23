<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\Surcharge;
use App\Models\Tariff;
use App\Models\Vendor;
use App\Modules\OMS\Domain\Exceptions\CreditLimitExceededException;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Contracts\FinancialLedgerRecorder;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ServiceType;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\Shared\Domain\Enums\SurchargeCalcMode;
use App\Modules\Shared\Domain\ValueObjects\AwbNumber;
use App\Modules\Shared\Domain\ValueObjects\Dimensions;
use DomainException;

final readonly class BookShipmentUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ShipmentRepository $shipments,
        private TrackingLogger $trackingLogger,
        private FinancialLedgerRecorder $ledgerRecorder,
    ) {}

    /**
     * @param  array{
     *     customer_id: int,
     *     origin_branch_id: int,
     *     destination_branch_id: int,
     *     vendor_id?: int|null,
     *     vendor_awb_no?: string|null,
     *     vendor_cost?: float|null,
     *     reference_no?: string|null,
     *     shipper_name: string,
     *     shipper_phone: string,
     *     shipper_address: string,
     *     consignee_name: string,
     *     consignee_phone: string,
     *     consignee_address: string,
     *     service_type: string,
     *     payment_method: string,
     *     declared_value?: float|null,
     *     cash_received?: bool|null,
     *     surcharge_ids?: list<int>,
     *     additional_charges?: list<array{description: string, amount: float, notes?: string|null}>,
     *     items: list<array{description: string, qty: int, actual_weight_kg: float, length_cm: float, width_cm: float, height_cm: float}>
     * }  $payload
     */
    public function execute(array $payload, ?int $actorId = null): Shipment
    {
        $paymentMethod = PaymentMethod::from($payload['payment_method']);
        $serviceType = ServiceType::from($payload['service_type']);

        return $this->transactions->run(function () use ($payload, $paymentMethod, $serviceType, $actorId): Shipment {
            /** @var Customer $customer */
            $customer = Customer::query()->lockForUpdate()->findOrFail($payload['customer_id']);
            /** @var Branch $originBranch */
            $originBranch = Branch::query()->findOrFail($payload['origin_branch_id']);
            /** @var Branch $destBranch */
            $destBranch = Branch::query()->findOrFail($payload['destination_branch_id']);

            $vendorId = isset($payload['vendor_id']) && (int) $payload['vendor_id'] > 0
                ? (int) $payload['vendor_id']
                : null;
            if ($vendorId !== null) {
                Vendor::query()->where('is_active', true)->findOrFail($vendorId);
            }

            if ($paymentMethod === PaymentMethod::TOP && ! $customer->is_top_enabled) {
                throw new DomainException("Pelanggan {$customer->name} belum memiliki kontrak aktif untuk metode B2B TOP.");
            }

            $tariff = $this->shipments->findActiveTariff($originBranch->id, $destBranch->id, $serviceType->value);
            $divisor = $tariff !== null ? $tariff->volumetric_divisor : $serviceType->defaultDivisor();
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

            [$chargeLines, $surchargeCost, $additionalChargeCost] = $this->buildChargeLines(
                tariff: $tariff,
                shippingCost: $shippingCost,
                chargeableWeight: $chargeableWeight,
                surchargeIds: $payload['surcharge_ids'] ?? [],
                additionalCharges: $payload['additional_charges'] ?? [],
            );

            $totalAmount = round($shippingCost + $insuranceCost + $surchargeCost + $additionalChargeCost, 2);

            // BR-02: Credit Limit Enforcement for TOP customers
            if ($paymentMethod === PaymentMethod::TOP) {
                $exposure = $this->shipments->customerCreditExposure($customer->id) + $totalAmount;
                $creditLimit = (float) $customer->credit_limit;
                if ($exposure > $creditLimit) {
                    throw CreditLimitExceededException::forCustomer($customer->name, $exposure, $creditLimit);
                }
            }

            $dateYmd = now()->format('ymd');
            $todayPrefix = 'PCH-'.strtoupper(substr($originBranch->code, 0, 3))."-{$dateYmd}-";
            $nextSeq = $this->shipments->nextDailySequence($todayPrefix);
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
                'vendor_id' => $vendorId,
                'vendor_awb_no' => $vendorId !== null ? ($payload['vendor_awb_no'] ?? null) : null,
                'vendor_cost' => $vendorId !== null ? round(max(0.0, (float) ($payload['vendor_cost'] ?? 0)), 2) : 0,
                'shipper_name' => $payload['shipper_name'],
                'shipper_phone' => $payload['shipper_phone'],
                'shipper_address' => $payload['shipper_address'],
                'consignee_name' => $payload['consignee_name'],
                'consignee_phone' => $payload['consignee_phone'],
                'consignee_address' => $payload['consignee_address'],
                'service_type' => $serviceType->value,
                'volumetric_divisor' => $divisor,
                'actual_weight_kg' => round($totalActual, 3),
                'volumetric_weight_kg' => round($totalVolumetric, 3),
                'chargeable_weight_kg' => round($chargeableWeight, 3),
                'shipping_cost' => $shippingCost,
                'insurance_cost' => $insuranceCost,
                'surcharge_cost' => $surchargeCost,
                'additional_charge_cost' => $additionalChargeCost,
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

            foreach ($chargeLines as $line) {
                $shipment->charges()->create([
                    ...$line,
                    'created_by' => $actorId,
                ]);
            }

            $this->trackingLogger->record(
                shipment: $shipment,
                status: ShipmentStatus::BOOKED,
                location: "{$originBranch->name} ({$originBranch->city})",
                description: "Resi diterbitkan (Non-COD · {$paymentMethod->label()} · Status Bayar: {$paymentStatus->label()}).",
                branchId: $originBranch->id,
                actorId: $actorId,
                latitude: $originBranch->latitude !== null ? (float) $originBranch->latitude : null,
                longitude: $originBranch->longitude !== null ? (float) $originBranch->longitude : null,
            );

            if ($paymentStatus === PaymentStatus::PAID) {
                $this->ledgerRecorder->recordCredit(
                    prefix: 'FIN',
                    branchId: $originBranch->id,
                    channel: $paymentMethod->value,
                    amount: $totalAmount,
                    referenceType: 'shipment',
                    referenceId: $shipment->id,
                    description: "Penerimaan tunai di muka loket asal untuk resi {$shipment->awb_number}",
                    actorId: $actorId,
                );
            }

            return $shipment->load(['items', 'charges', 'vendor', 'originBranch', 'destinationBranch', 'customer']);
        });
    }

    /**
     * @param  list<int>  $surchargeIds
     * @param  list<array{description: string, amount: float, notes?: string|null}>  $additionalCharges
     * @return array{0: list<array<string, mixed>>, 1: float, 2: float}
     */
    private function buildChargeLines(
        ?Tariff $tariff,
        float $shippingCost,
        float $chargeableWeight,
        array $surchargeIds,
        array $additionalCharges,
    ): array {
        $lines = [];
        $surchargeCost = 0.0;
        $additionalChargeCost = 0.0;

        if ($tariff !== null) {
            $fuelPct = (float) $tariff->fuel_surcharge_pct;
            if ($fuelPct > 0) {
                $fuelAmt = round($shippingCost * ($fuelPct / 100), 2);
                $surchargeCost += $fuelAmt;
                $lines[] = [
                    'surcharge_id' => null,
                    'charge_type' => 'surcharge',
                    'code' => 'TARIFF-FSC',
                    'description' => "Fuel Surcharge Rute ({$fuelPct}%)",
                    'calc_mode' => SurchargeCalcMode::PERCENTAGE->value,
                    'rate_value' => $fuelPct,
                    'quantity' => 1,
                    'amount' => $fuelAmt,
                    'notes' => 'Otomatis dari pengaturan matriks tarif.',
                ];
            }

            $handlingFee = (float) $tariff->handling_fee;
            if ($handlingFee > 0) {
                $surchargeCost += $handlingFee;
                $lines[] = [
                    'surcharge_id' => null,
                    'charge_type' => 'surcharge',
                    'code' => 'TARIFF-HND',
                    'description' => 'Handling Fee Rute',
                    'calc_mode' => SurchargeCalcMode::FIXED->value,
                    'rate_value' => $handlingFee,
                    'quantity' => 1,
                    'amount' => round($handlingFee, 2),
                    'notes' => 'Otomatis dari pengaturan matriks tarif.',
                ];
            }

            $heavyThreshold = $tariff->heavy_weight_threshold_kg !== null ? (float) $tariff->heavy_weight_threshold_kg : null;
            $heavyRate = (float) $tariff->heavy_weight_surcharge_per_kg;
            if ($heavyThreshold !== null && $chargeableWeight > $heavyThreshold && $heavyRate > 0) {
                $excessKg = $chargeableWeight - $heavyThreshold;
                $heavyAmt = round($excessKg * $heavyRate, 2);
                $surchargeCost += $heavyAmt;
                $lines[] = [
                    'surcharge_id' => null,
                    'charge_type' => 'surcharge',
                    'code' => 'TARIFF-HVY',
                    'description' => "Heavy Weight Surcharge (>{$heavyThreshold} kg)",
                    'calc_mode' => SurchargeCalcMode::PER_KG->value,
                    'rate_value' => $heavyRate,
                    'quantity' => $excessKg,
                    'amount' => $heavyAmt,
                    'notes' => 'Otomatis dari pengaturan matriks tarif.',
                ];
            }
        }

        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $surchargeIds))));
        if ($cleanIds !== []) {
            $selected = Surcharge::query()->whereIn('id', $cleanIds)->where('is_active', true)->get();
            foreach ($selected as $surcharge) {
                $amt = $surcharge->computeAmount($shippingCost, $chargeableWeight);
                if ($amt <= 0) {
                    continue;
                }
                $surchargeCost += $amt;
                $lines[] = [
                    'surcharge_id' => $surcharge->id,
                    'charge_type' => 'surcharge',
                    'code' => $surcharge->code,
                    'description' => $surcharge->name,
                    'calc_mode' => $surcharge->calc_mode->value,
                    'rate_value' => (float) $surcharge->amount,
                    'quantity' => $surcharge->calc_mode === SurchargeCalcMode::PER_KG ? $chargeableWeight : 1,
                    'amount' => $amt,
                    'notes' => $surcharge->description,
                ];
            }
        }

        foreach ($additionalCharges as $addCharge) {
            $desc = trim($addCharge['description']);
            $amt = round((float) $addCharge['amount'], 2);
            if ($desc === '' || $amt <= 0) {
                continue;
            }
            $additionalChargeCost += $amt;
            $lines[] = [
                'surcharge_id' => null,
                'charge_type' => 'add_charge',
                'code' => 'ADD-CHG',
                'description' => $desc,
                'calc_mode' => SurchargeCalcMode::FIXED->value,
                'rate_value' => $amt,
                'quantity' => 1,
                'amount' => $amt,
                'notes' => $addCharge['notes'] ?? null,
            ];
        }

        return [$lines, round($surchargeCost, 2), round($additionalChargeCost, 2)];
    }
}
