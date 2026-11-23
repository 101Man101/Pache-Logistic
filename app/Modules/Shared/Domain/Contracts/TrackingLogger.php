<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Contracts;

use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;

interface TrackingLogger
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
    ): TrackingLog;
}
