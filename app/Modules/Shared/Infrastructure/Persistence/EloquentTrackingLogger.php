<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;

final readonly class EloquentTrackingLogger implements TrackingLogger
{
    public function record(
        Shipment $shipment,
        ShipmentStatus $status,
        string $location,
        string $description,
        ?int $branchId = null,
        ?int $actorId = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): TrackingLog {
        /** @var TrackingLog */
        return TrackingLog::query()->create([
            'shipment_id' => $shipment->id,
            'branch_id' => $branchId ?? $shipment->origin_branch_id,
            'status' => $status->value,
            'location' => $location,
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'actor_id' => $actorId,
            'created_at' => now(),
        ]);
    }
}
