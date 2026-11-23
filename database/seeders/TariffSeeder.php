<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Tariff;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->get()->keyBy('code');

        // Rate Engine Matrix (No COD Fee — Strictly Non-COD)
        $routeRates = [
            ['JKT', 'BDG', 9000, 16000, 6500, 1, 1, 2],
            ['JKT', 'SBY', 15000, 28000, 9500, 2, 1, 4],
            ['JKT', 'SMG', 12000, 22000, 8000, 2, 1, 3],
            ['JKT', 'MDN', 24000, 42000, 14500, 4, 2, 6],
            ['JKT', 'MKS', 29000, 49000, 17500, 4, 2, 7],
            ['BDG', 'JKT', 9000, 16000, 6500, 1, 1, 2],
            ['BDG', 'SBY', 15500, 29000, 9800, 2, 1, 4],
            ['SBY', 'JKT', 15000, 28000, 9500, 2, 1, 4],
            ['SBY', 'MKS', 22000, 38000, 13000, 3, 2, 5],
            ['MDN', 'JKT', 24000, 42000, 14500, 4, 2, 6],
        ];

        foreach ($routeRates as [$origCode, $destCode, $regRate, $expRate, $crgRate, $regSla, $expSla, $crgSla]) {
            $origin = $branches->get($origCode);
            $destination = $branches->get($destCode);
            if ($origin === null || $destination === null) {
                continue;
            }

            $services = [
                ['REG', 6000, 1, $regRate, $regSla, 0, 0, null, 0],
                ['EXP', 5000, 1, $expRate, $expSla, 5, 10000, null, 0],
                ['CRG', 4000, 10, $crgRate, $crgSla, 0, 25000, 100, 1500],
            ];

            foreach ($services as [$svc, $div, $minW, $rate, $sla, $fuelPct, $handlingFee, $heavyThreshold, $heavyRate]) {
                Tariff::query()->updateOrCreate(
                    [
                        'origin_branch_id' => $origin->id,
                        'destination_branch_id' => $destination->id,
                        'service_type' => $svc,
                    ],
                    [
                        'volumetric_divisor' => $div,
                        'min_weight_kg' => $minW,
                        'rate_per_kg' => $rate,
                        'insurance_rate_pct' => 0.25,
                        'fuel_surcharge_pct' => $fuelPct,
                        'handling_fee' => $handlingFee,
                        'heavy_weight_threshold_kg' => $heavyThreshold,
                        'heavy_weight_surcharge_per_kg' => $heavyRate,
                        'sla_days' => $sla,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
