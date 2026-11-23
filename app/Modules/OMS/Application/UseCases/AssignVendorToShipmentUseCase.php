<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\Shipment;
use App\Models\Vendor;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;

final readonly class AssignVendorToShipmentUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ShipmentRepository $shipments,
        private TrackingLogger $trackingLogger,
    ) {}

    public function execute(
        Shipment $shipment,
        ?int $vendorId,
        ?string $vendorAwbNo,
        float $vendorCost,
        ?int $actorId = null,
    ): ?Vendor {
        return $this->transactions->run(function () use ($shipment, $vendorId, $vendorAwbNo, $vendorCost, $actorId): ?Vendor {
            $locked = $this->shipments->findForUpdateOrFail($shipment->id);

            $vendor = $vendorId !== null && $vendorId > 0
                ? Vendor::query()->find($vendorId)
                : null;

            $locked->update([
                'vendor_id' => $vendor?->id,
                'vendor_awb_no' => $vendor !== null ? $vendorAwbNo : null,
                'vendor_cost' => $vendor !== null ? round(max(0.0, $vendorCost), 2) : 0,
            ]);

            if ($vendor !== null) {
                $location = $locked->originBranch ? "{$locked->originBranch->name} ({$locked->originBranch->city})" : 'Pache Logistic';
                $description = "Ditugaskan ke Vendor 3PL {$vendor->name}".($locked->vendor_awb_no ? " (Resi Vendor: {$locked->vendor_awb_no})" : '.');

                $this->trackingLogger->record(
                    shipment: $locked,
                    status: $locked->status,
                    location: $location,
                    description: $description,
                    branchId: $locked->origin_branch_id,
                    actorId: $actorId,
                );
            }

            return $vendor;
        });
    }
}
