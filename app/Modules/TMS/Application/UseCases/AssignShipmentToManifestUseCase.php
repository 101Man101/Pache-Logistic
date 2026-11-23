<?php

declare(strict_types=1);

namespace App\Modules\TMS\Application\UseCases;

use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Modules\OMS\Domain\Exceptions\UnpaidShipmentDispatchException;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\ManifestStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\TMS\Domain\Repositories\ManifestRepository;
use DomainException;

/**
 * Scan Bagging into Manifest with BR-08 Non-COD Dispatch Gate & Route Check (FR-TMS-02).
 */
final readonly class AssignShipmentToManifestUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ManifestRepository $manifests,
        private ShipmentRepository $shipments,
        private TrackingLogger $trackingLogger,
    ) {}

    public function execute(Manifest $manifest, string $awbNumber, ?int $actorId = null): ManifestItem
    {
        return $this->transactions->run(function () use ($manifest, $awbNumber, $actorId): ManifestItem {
            $lockedManifest = $this->manifests->findForUpdateOrFail($manifest->id);

            if ($lockedManifest->status !== ManifestStatus::OPEN) {
                throw new DomainException("Manifest {$lockedManifest->manifest_number} sudah diberangkatkan dan terkunci.");
            }

            $shipment = $this->shipments->findByAwbForUpdate($awbNumber);
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

            if ($this->manifests->hasShipment($lockedManifest->id, $shipment->id)) {
                throw new DomainException("Duplicate scan: Resi {$shipment->awb_number} sudah masuk dalam Manifest ini.");
            }

            if (in_array($shipment->status, [ShipmentStatus::BOOKED, ShipmentStatus::PICKED_UP], true)) {
                $shipment->update(['status' => ShipmentStatus::ORIGIN_HUB]);
            }

            $item = $this->manifests->attachShipment($lockedManifest->id, $shipment->id);

            $this->trackingLogger->record(
                shipment: $shipment,
                status: ShipmentStatus::ORIGIN_HUB,
                location: "{$lockedManifest->originBranch?->name} — Sorting Hub",
                description: "Paket di-scan masuk Manifest {$lockedManifest->manifest_number} menuju {$lockedManifest->destinationBranch?->name}.",
                branchId: $lockedManifest->origin_branch_id,
                actorId: $actorId,
            );

            return $item;
        });
    }
}
