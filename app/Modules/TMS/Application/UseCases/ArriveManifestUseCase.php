<?php

declare(strict_types=1);

namespace App\Modules\TMS\Application\UseCases;

use App\Models\Manifest;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\ManifestStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\TMS\Domain\Repositories\ManifestRepository;

final readonly class ArriveManifestUseCase
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

            if ($locked->status !== ManifestStatus::DEPARTED) {
                return $locked;
            }

            $locked->update([
                'status' => ManifestStatus::ARRIVED,
                'arrived_at' => now(),
            ]);

            foreach ($locked->items as $item) {
                $item->update(['unloaded_at' => now()]);
                if ($item->shipment === null) {
                    continue;
                }

                $item->shipment->update(['status' => ShipmentStatus::DESTINATION_HUB]);
                $this->trackingLogger->record(
                    shipment: $item->shipment,
                    status: ShipmentStatus::DESTINATION_HUB,
                    location: "{$locked->destinationBranch?->name} ({$locked->destinationBranch?->city})",
                    description: "Manifest {$locked->manifest_number} tiba di Hub tujuan & selesai unloading.",
                    branchId: $locked->destination_branch_id,
                    actorId: $actorId,
                );
            }

            return $locked->refresh();
        });
    }
}
