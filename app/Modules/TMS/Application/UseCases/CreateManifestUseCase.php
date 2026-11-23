<?php

declare(strict_types=1);

namespace App\Modules\TMS\Application\UseCases;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Manifest;
use App\Models\Vehicle;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\ManifestStatus;
use App\Modules\TMS\Domain\Exceptions\FleetComplianceException;
use App\Modules\TMS\Domain\Repositories\ManifestRepository;

/**
 * Create Linehaul Manifest with BR-06 Vehicle/Driver Compliance Enforcement.
 */
final readonly class CreateManifestUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ManifestRepository $manifests,
    ) {}

    /**
     * @param  array{
     *     origin_branch_id: int,
     *     destination_branch_id: int,
     *     vehicle_id: int,
     *     driver_id: int,
     *     vendor_id?: int|null,
     *     seal_number?: string|null,
     *     driver_advance_amount?: float,
     *     vendor_cost?: float
     * }  $payload
     */
    public function execute(array $payload, ?int $actorId = null): Manifest
    {
        return $this->transactions->run(function () use ($payload, $actorId): Manifest {
            /** @var Vehicle $vehicle */
            $vehicle = Vehicle::query()->findOrFail($payload['vehicle_id']);
            /** @var Driver $driver */
            $driver = Driver::query()->findOrFail($payload['driver_id']);
            /** @var Branch $origin */
            $origin = Branch::query()->findOrFail($payload['origin_branch_id']);
            /** @var Branch $dest */
            $dest = Branch::query()->findOrFail($payload['destination_branch_id']);

            // BR-06: Block expired STNK, KIR, or Driver SIM
            if ($vehicle->stnk_expires_at->isPast()) {
                throw FleetComplianceException::expiredDocument($vehicle->plate_number, 'STNK', $vehicle->stnk_expires_at->format('d M Y'));
            }
            if ($vehicle->kir_expires_at->isPast()) {
                throw FleetComplianceException::expiredDocument($vehicle->plate_number, 'Uji KIR', $vehicle->kir_expires_at->format('d M Y'));
            }
            if ($driver->sim_expires_at->isPast()) {
                throw FleetComplianceException::expiredDocument($driver->name, 'SIM', $driver->sim_expires_at->format('d M Y'));
            }

            $prefix = "MNF-{$origin->code}-{$dest->code}-".now()->format('ymd').'-';
            $seq = $this->manifests->nextSequence($prefix);
            $manifestNumber = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

            $vendorId = isset($payload['vendor_id']) && (int) $payload['vendor_id'] > 0
                ? (int) $payload['vendor_id']
                : null;

            return $this->manifests->create([
                'manifest_number' => $manifestNumber,
                'origin_branch_id' => $origin->id,
                'destination_branch_id' => $dest->id,
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'vendor_id' => $vendorId,
                'seal_number' => $payload['seal_number'] ?? 'SEAL-'.strtoupper(substr(md5($manifestNumber), 0, 6)),
                'driver_advance_amount' => (float) ($payload['driver_advance_amount'] ?? 0),
                'vendor_cost' => $vendorId !== null ? round(max(0.0, (float) ($payload['vendor_cost'] ?? 0)), 2) : 0,
                'status' => ManifestStatus::OPEN,
                'created_by' => $actorId,
            ]);
        });
    }
}
