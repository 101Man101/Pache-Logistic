<?php

declare(strict_types=1);

namespace App\Modules\TMS\Application\UseCases;

use App\Models\Shipment;
use App\Modules\OMS\Domain\Exceptions\UnpaidShipmentDispatchException;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use DomainException;

/**
 * Mandatory Digital e-POD Submission (BR-07) — Strictly Non-COD (Zero Cash Collection).
 */
final readonly class SubmitEpodUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ShipmentRepository $shipments,
        private TrackingLogger $trackingLogger,
    ) {}

    /**
     * @param  array{
     *     receiver_name: string,
     *     receiver_relation: string,
     *     latitude: float,
     *     longitude: float,
     *     notes?: string|null,
     *     photo_url?: string|null
     * }  $payload
     */
    public function execute(Shipment $shipment, array $payload, ?int $actorId = null): Shipment
    {
        return $this->transactions->run(function () use ($shipment, $payload, $actorId): Shipment {
            $locked = $this->shipments->findForUpdateOrFail($shipment->id);

            if (! $locked->payment_status->isClearedForDispatch()) {
                throw UnpaidShipmentDispatchException::forAwb($locked->awb_number);
            }

            if (trim($payload['receiver_name']) === '' || trim($payload['receiver_relation']) === '') {
                throw new DomainException('Syarat e-POD (BR-07): Nama penerima dan relasi wajib diisi.');
            }

            $locked->update([
                'status' => ShipmentStatus::DELIVERED,
                'delivered_at' => now(),
                'epod_receiver_name' => $payload['receiver_name'],
                'epod_receiver_relation' => $payload['receiver_relation'],
                'epod_latitude' => $payload['latitude'],
                'epod_longitude' => $payload['longitude'],
                'epod_notes' => $payload['notes'] ?? null,
                'epod_photo_url' => $payload['photo_url'] ?? '/images/epod-verified.svg',
            ]);

            $this->trackingLogger->record(
                shipment: $locked,
                status: ShipmentStatus::DELIVERED,
                location: "{$locked->destinationBranch?->city} (GPS {$payload['latitude']}, {$payload['longitude']})",
                description: "Paket diterima oleh {$payload['receiver_name']} ({$payload['receiver_relation']}) — e-POD Non-COD terverifikasi.",
                branchId: $locked->destination_branch_id,
                actorId: $actorId,
                latitude: $payload['latitude'],
                longitude: $payload['longitude'],
            );

            return $locked->refresh();
        });
    }
}
