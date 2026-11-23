<?php

declare(strict_types=1);

namespace App\Modules\TMS\Infrastructure\Persistence;

use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Modules\TMS\Domain\Repositories\ManifestRepository;

final readonly class EloquentManifestRepository implements ManifestRepository
{
    public function findForUpdateOrFail(int $id): Manifest
    {
        /** @var Manifest */
        return Manifest::query()
            ->with(['originBranch', 'destinationBranch', 'items.shipment'])
            ->lockForUpdate()
            ->findOrFail($id);
    }

    public function nextSequence(string $prefix): int
    {
        $lastManifest = Manifest::query()
            ->where('manifest_number', 'like', "{$prefix}%")
            ->orderByDesc('manifest_number')
            ->lockForUpdate()
            ->value('manifest_number');

        return is_string($lastManifest) ? ((int) substr($lastManifest, -3) + 1) : 1;
    }

    public function create(array $attributes): Manifest
    {
        /** @var Manifest */
        return Manifest::query()->create($attributes);
    }

    public function hasShipment(int $manifestId, int $shipmentId): bool
    {
        return ManifestItem::query()
            ->where('manifest_id', $manifestId)
            ->where('shipment_id', $shipmentId)
            ->exists();
    }

    public function attachShipment(int $manifestId, int $shipmentId): ManifestItem
    {
        /** @var ManifestItem */
        return ManifestItem::query()->create([
            'manifest_id' => $manifestId,
            'shipment_id' => $shipmentId,
            'scanned_at' => now(),
        ]);
    }
}
