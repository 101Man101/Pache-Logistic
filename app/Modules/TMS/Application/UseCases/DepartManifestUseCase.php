<?php

declare(strict_types=1);

namespace App\Modules\TMS\Application\UseCases;

use App\Models\Manifest;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\ManifestStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\TMS\Domain\Repositories\ManifestRepository;

final readonly class DepartManifestUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ManifestRepository $manifests,
        private TrackingLogger $trackingLogger,
    ) {}

    public function execute(Manifest $manifest, ?int $actorId = null): Manifest
    {
        return $this->transactions->run(function () use ($manifest, $actorId): Manifest {
            $locked = $this->manifests->findForUpdateOrFail($manifest->id);

            if ($locked->status !== ManifestStatus::OPEN) {
                return $locked;
            }

            $locked->update([
                'status' => ManifestStatus::DEPARTED,
                'departed_at' => now(),
            ]);

            foreach ($locked->items as $item) {
                if ($item->shipment === null) {
                    continue;
                }

                $item->shipment->update(['status' => ShipmentStatus::IN_TRANSIT]);
                $this->trackingLogger->record(
                    shipment: $item->shipment,
                    status: ShipmentStatus::IN_TRANSIT,
                    location: "Linehaul {$locked->originBranch?->city} → {$locked->destinationBranch?->city}",
                    description: "Diberangkatkan dengan Manifest {$locked->manifest_number} (Segel {$locked->seal_number}).",
                    branchId: $locked->origin_branch_id,
                    actorId: $actorId,
                );
            }

            return $locked->refresh();
        });
    }
}
