<?php

declare(strict_types=1);

namespace App\Modules\TMS\Domain\Repositories;

use App\Models\Manifest;
use App\Models\ManifestItem;

interface ManifestRepository
{
    public function findForUpdateOrFail(int $id): Manifest;

    public function nextSequence(string $prefix): int;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Manifest;

    public function hasShipment(int $manifestId, int $shipmentId): bool;

    public function attachShipment(int $manifestId, int $shipmentId): ManifestItem;
}
