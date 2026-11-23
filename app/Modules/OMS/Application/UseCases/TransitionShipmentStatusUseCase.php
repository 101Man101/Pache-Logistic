<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\Shipment;
use App\Modules\OMS\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\OMS\Domain\Exceptions\UnpaidShipmentDispatchException;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;

final readonly class TransitionShipmentStatusUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ShipmentRepository $shipments,
        private TrackingLogger $trackingLogger,
    ) {}

    public function execute(
        Shipment $shipment,
        ShipmentStatus $nextStatus,
        string $location,
        string $description,
        ?int $branchId = null,
        ?int $actorId = null,
        bool $allowManual = false,
    ): Shipment {
        return $this->transactions->run(function () use ($shipment, $nextStatus, $location, $description, $branchId, $actorId, $allowManual): Shipment {
            $locked = $this->shipments->findForUpdateOrFail($shipment->id);

            // BR-03: Strict Shipment State Machine unless explicitly updated manually
            if (! $allowManual && ! $locked->status->canTransitionTo($nextStatus)) {
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

            $this->trackingLogger->record(
                shipment: $locked,
                status: $nextStatus,
                location: $location,
                description: $description,
                branchId: $branchId ?? $locked->origin_branch_id,
                actorId: $actorId,
            );

            return $locked->refresh();
        });
    }
}
