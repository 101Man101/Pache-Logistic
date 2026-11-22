<?php

declare(strict_types=1);

namespace App\Modules\TMS\Application\UseCases;

use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Modules\OMS\Domain\Exceptions\UnpaidShipmentDispatchException;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Scan Bagging into Manifest with BR-08 Non-COD Dispatch Gate & Route Check (FR-TMS-02).
 */
final readonly class AssignShipmentToManifestUseCase
{
    public function execute(Manifest $manifest, string $awbNumber, ?int $actorId = null): ManifestItem
    {
        return DB::transaction(function () use ($manifest, $awbNumber, $actorId): ManifestItem {
            /** @var Manifest $lockedManifest */
            $lockedManifest = Manifest::query()->with(['originBranch', 'destinationBranch'])->lockForUpdate()->findOrFail($manifest->id);

            if ($lockedManifest->status !== 'open') {
                throw new DomainException("Manifest {$lockedManifest->manifest_number} sudah diberangkatkan dan terkunci.");
            }

            /** @var Shipment|null $shipment */
            $shipment = Shipment::query()->where('awb_number', trim($awbNumber))->lockForUpdate()->first();
            if ($shipment === null) {
                throw new DomainException("Resi {$awbNumber} tidak ditemukan di sistem Pache Logistic.");
            }

            // BR-08: Non-COD Dispatch Gate
            if (! $shipment->payment_status->isClearedForDispatch()) {
                throw UnpaidShipmentDispatchException::forAwb($shipment->awb_number);
            }

            // FR-TMS-02: Destination hub validation
            if ($shipment->destination_branch_id !== $lockedManifest->destination_branch_id) {
                throw new DomainException(
                    "Salah rute Hub tujuan: Resi {$shipment->awb_number} tidak menuju {$lockedManifest->destinationBranch?->name}."
                );
            }

            $existing = ManifestItem::query()
                ->where('manifest_id', $lockedManifest->id)
                ->where('shipment_id', $shipment->id)
                ->first();

            if ($existing !== null) {
                throw new DomainException("Duplicate scan: Resi {$shipment->awb_number} sudah masuk dalam Manifest ini.");
            }

            if (in_array($shipment->status, [ShipmentStatus::BOOKED, ShipmentStatus::PICKED_UP], true)) {
                $shipment->update(['status' => ShipmentStatus::ORIGIN_HUB]);
            }

            /** @var ManifestItem $item */
            $item = ManifestItem::query()->create([
                'manifest_id' => $lockedManifest->id,
                'shipment_id' => $shipment->id,
                'scanned_at' => now(),
            ]);

            TrackingLog::query()->create([
                'shipment_id' => $shipment->id,
                'branch_id' => $lockedManifest->origin_branch_id,
                'status' => ShipmentStatus::ORIGIN_HUB->value,
                'location' => "{$lockedManifest->originBranch?->name} — Sorting Hub",
                'description' => "Paket di-scan masuk Manifest {$lockedManifest->manifest_number} menuju {$lockedManifest->destinationBranch?->name}.",
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            return $item;
        });
    }
}
