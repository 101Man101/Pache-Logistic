<?php

declare(strict_types=1);

namespace App\Modules\TMS\Application\UseCases;

use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Modules\OMS\Domain\Exceptions\UnpaidShipmentDispatchException;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Mandatory Digital e-POD Submission (BR-07) — Strictly Non-COD (Zero Cash Collection).
 */
final readonly class SubmitEpodUseCase
{
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
        return DB::transaction(function () use ($shipment, $payload, $actorId): Shipment {
            /** @var Shipment $locked */
            $locked = Shipment::query()->with('destinationBranch')->lockForUpdate()->findOrFail($shipment->id);

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

            TrackingLog::query()->create([
                'shipment_id' => $locked->id,
                'branch_id' => $locked->destination_branch_id,
                'status' => ShipmentStatus::DELIVERED->value,
                'location' => "{$locked->destinationBranch?->city} (GPS {$payload['latitude']}, {$payload['longitude']})",
                'description' => "Paket diterima oleh {$payload['receiver_name']} ({$payload['receiver_relation']}) — e-POD Non-COD terverifikasi.",
                'latitude' => $payload['latitude'],
                'longitude' => $payload['longitude'],
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            return $locked->refresh();
        });
    }
}
