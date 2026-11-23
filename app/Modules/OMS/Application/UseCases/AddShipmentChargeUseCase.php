<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\Shipment;
use App\Models\ShipmentCharge;
use App\Models\Surcharge;
use App\Modules\OMS\Domain\Exceptions\CreditLimitExceededException;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\Shared\Domain\Enums\SurchargeCalcMode;
use DomainException;

final readonly class AddShipmentChargeUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ShipmentRepository $shipments,
        private TrackingLogger $trackingLogger,
    ) {}

    /**
     * @param  array{
     *     surcharge_id?: int|null,
     *     charge_type?: string,
     *     description?: string|null,
     *     amount?: float|null,
     *     notes?: string|null
     * }  $payload
     */
    public function add(Shipment $shipment, array $payload, ?int $actorId = null): ShipmentCharge
    {
        return $this->transactions->run(function () use ($shipment, $payload, $actorId): ShipmentCharge {
            $locked = $this->shipments->findForUpdateOrFail($shipment->id);
            $this->assertMutable($locked);

            $surchargeId = isset($payload['surcharge_id']) && (int) $payload['surcharge_id'] > 0
                ? (int) $payload['surcharge_id']
                : null;

            if ($surchargeId !== null) {
                /** @var Surcharge $surcharge */
                $surcharge = Surcharge::query()->findOrFail($surchargeId);
                $chargeType = 'surcharge';
                $code = $surcharge->code;
                $description = trim((string) ($payload['description'] ?? '')) ?: $surcharge->name;
                $calcMode = $surcharge->calc_mode->value;
                $rateValue = (float) $surcharge->amount;
                $quantity = $surcharge->calc_mode === SurchargeCalcMode::PER_KG ? (float) $locked->chargeable_weight_kg : 1.0;
                $amount = isset($payload['amount']) && (float) $payload['amount'] > 0
                    ? round((float) $payload['amount'], 2)
                    : $surcharge->computeAmount((float) $locked->shipping_cost, (float) $locked->chargeable_weight_kg);
            } else {
                $chargeType = ($payload['charge_type'] ?? 'add_charge') === 'surcharge' ? 'surcharge' : 'add_charge';
                $code = $chargeType === 'surcharge' ? 'MANUAL-SUR' : 'ADD-CHG';
                $description = trim((string) ($payload['description'] ?? ''));
                if ($description === '') {
                    throw new DomainException('Deskripsi biaya tambahan wajib diisi.');
                }
                $calcMode = SurchargeCalcMode::FIXED->value;
                $amount = round((float) ($payload['amount'] ?? 0), 2);
                if ($amount <= 0) {
                    throw new DomainException('Nominal biaya tambahan harus lebih dari 0.');
                }
                $rateValue = $amount;
                $quantity = 1.0;
            }

            if ($locked->payment_method === PaymentMethod::TOP && $locked->customer !== null) {
                $exposure = $this->shipments->customerCreditExposure($locked->customer->id) + $amount;
                $limit = (float) $locked->customer->credit_limit;
                if ($exposure > $limit) {
                    throw CreditLimitExceededException::forCustomer($locked->customer->name, $exposure, $limit);
                }
            }

            /** @var ShipmentCharge $charge */
            $charge = $locked->charges()->create([
                'surcharge_id' => $surchargeId,
                'charge_type' => $chargeType,
                'code' => $code,
                'description' => $description,
                'calc_mode' => $calcMode,
                'rate_value' => $rateValue,
                'quantity' => $quantity,
                'amount' => $amount,
                'notes' => $payload['notes'] ?? null,
                'created_by' => $actorId,
            ]);

            $this->recalculateTotals($locked);

            $location = $locked->originBranch ? "{$locked->originBranch->name} ({$locked->originBranch->city})" : 'Pache Logistic';
            $this->trackingLogger->record(
                shipment: $locked,
                status: $locked->status,
                location: $location,
                description: sprintf('Penambahan biaya (%s): %s sebesar Rp %s.', strtoupper($chargeType), $description, number_format($amount, 0, ',', '.')),
                branchId: $locked->origin_branch_id,
                actorId: $actorId,
            );

            return $charge;
        });
    }

    public function remove(Shipment $shipment, ShipmentCharge $charge): void
    {
        $this->transactions->run(function () use ($shipment, $charge): void {
            $locked = $this->shipments->findForUpdateOrFail($shipment->id);
            $this->assertMutable($locked);

            if ($charge->shipment_id !== $locked->id) {
                throw new DomainException('Item biaya tidak termasuk pada resi ini.');
            }

            $charge->delete();
            $this->recalculateTotals($locked);
        });
    }

    private function assertMutable(Shipment $shipment): void
    {
        if ($shipment->status === ShipmentStatus::CANCELLED) {
            throw new DomainException('Tidak dapat mengubah biaya pada resi yang dibatalkan.');
        }

        if ($shipment->invoiceLine()->exists()) {
            throw new DomainException('Resi sudah masuk ke faktur B2B TOP sehingga rincian biaya terkunci.');
        }
    }

    private function recalculateTotals(Shipment $shipment): void
    {
        $surchargeCost = round((float) $shipment->charges()->where('charge_type', 'surcharge')->sum('amount'), 2);
        $addChargeCost = round((float) $shipment->charges()->where('charge_type', 'add_charge')->sum('amount'), 2);
        $totalAmount = round(
            (float) $shipment->shipping_cost + (float) $shipment->insurance_cost + $surchargeCost + $addChargeCost,
            2
        );

        $shipment->update([
            'surcharge_cost' => $surchargeCost,
            'additional_charge_cost' => $addChargeCost,
            'total_amount' => $totalAmount,
        ]);
    }
}
