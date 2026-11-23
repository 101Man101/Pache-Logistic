<?php

declare(strict_types=1);

namespace App\Modules\MDM\Application\UseCases;

use App\Models\Tariff;
use App\Modules\MDM\Domain\Repositories\MasterDataRepository;

final readonly class SaveMasterTariffUseCase
{
    public function __construct(
        private MasterDataRepository $masterData,
    ) {}

    /**
     * @param  array{
     *     origin_branch_id: int,
     *     destination_branch_id: int,
     *     service_type: string,
     *     volumetric_divisor: int,
     *     min_weight_kg: float,
     *     rate_per_kg: float,
     *     insurance_rate_pct?: float|null,
     *     fuel_surcharge_pct?: float|null,
     *     handling_fee?: float|null,
     *     heavy_weight_threshold_kg?: float|null,
     *     heavy_weight_surcharge_per_kg?: float|null,
     *     sla_days: int,
     *     is_active?: bool|null
     * }  $payload
     */
    public function execute(array $payload): Tariff
    {
        return $this->masterData->saveTariff(
            [
                'origin_branch_id' => $payload['origin_branch_id'],
                'destination_branch_id' => $payload['destination_branch_id'],
                'service_type' => $payload['service_type'],
            ],
            [
                'volumetric_divisor' => $payload['volumetric_divisor'],
                'min_weight_kg' => $payload['min_weight_kg'],
                'rate_per_kg' => $payload['rate_per_kg'],
                'insurance_rate_pct' => (float) ($payload['insurance_rate_pct'] ?? 0.25),
                'fuel_surcharge_pct' => (float) ($payload['fuel_surcharge_pct'] ?? 0),
                'handling_fee' => (float) ($payload['handling_fee'] ?? 0),
                'heavy_weight_threshold_kg' => $payload['heavy_weight_threshold_kg'] ?? null,
                'heavy_weight_surcharge_per_kg' => (float) ($payload['heavy_weight_surcharge_per_kg'] ?? 0),
                'sla_days' => $payload['sla_days'],
                'is_active' => (bool) ($payload['is_active'] ?? true),
            ]
        );
    }

    public function toggle(Tariff $tariff): Tariff
    {
        return $this->masterData->toggleTariff($tariff);
    }
}
