<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Modules\OMS\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\OMS\Domain\Exceptions\UnpaidShipmentDispatchException;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Illuminate\Support\Facades\DB;

final readonly class TransitionShipmentStatusUseCase
{
    public function execute(
        Shipment $shipment,
        ShipmentStatus $nextStatus,
        string $location,
        string $description,
        ?int $branchId = null,
        ?int $actorId = null,
    ): Shipment {
        return DB::transaction(function () use ($shipment, $nextStatus, $location, $description, $branchId, $actorId): Shipment {
            /** @var Shipment $locked */
            $locked = Shipment::query()->lockForUpdate()->findOrFail($shipment->id);

            // BR-03: Strict Shipment State Machine
            if (! $locked->status->canTransitionTo($nextStatus)) {
                throw InvalidStatusTransitionException::between($locked->status, $nextStatus);
            }

            // BR-08: Non-COD Dispatch Gate
            if ($nextStatus->requiresPaymentClearance() && ! $locked->payment_status->isClearedForDispatch()) {
                throw UnpaidShipmentDispatchException::forAwb($locked->awb_number);
            }

            $locked->update([
                'status' => $nextStatus,
                'delivered_at' => $nextStatus === ShipmentStatus::DELIVERED ? now() : $locked->delivered_at,
            ]);

            TrackingLog::query()->create([
                'shipment_id' => $locked->id,
                'branch_id' => $branchId ?? $locked->origin_branch_id,
                'status' => $nextStatus->value,
                'location' => $location,
                'description' => $description,
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            return $locked->refresh();
        });
    }
}
