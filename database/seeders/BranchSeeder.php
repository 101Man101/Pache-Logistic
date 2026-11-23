<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'code' => 'JKT',
                'name' => 'Jakarta Pusat Head Office',
                'type' => 'head_office',
                'city' => 'Jakarta',
                'address' => 'Jl. Jend. Sudirman Kav. 52, Jakarta Selatan',
                'phone' => '021-5501-8800',
                'latitude' => -6.2249120,
                'longitude' => 106.8091240,
            ],
            [
                'code' => 'BDG',
                'name' => 'Bandung Regional Hub',
                'type' => 'regional_hub',
                'city' => 'Bandung',
                'address' => 'Jl. Soekarno Hatta No. 412, Bandung',
                'phone' => '022-7510-2210',
                'latitude' => -6.9412890,
                'longitude' => 107.6387420,
            ],
            [
                'code' => 'SBY',
                'name' => 'Surabaya Regional Hub',
                'type' => 'regional_hub',
                'city' => 'Surabaya',
                'address' => 'Kawasan Industri Rungkut Blok C-14, Surabaya',
                'phone' => '031-8432-9900',
                'latitude' => -7.3264510,
                'longitude' => 112.7618820,
            ],
            [
                'code' => 'SMG',
                'name' => 'Semarang Sorting Center',
                'type' => 'sorting_center',
                'city' => 'Semarang',
                'address' => 'Jl. Kaligawe Raya KM 5, Semarang',
                'phone' => '024-6582-4110',
                'latitude' => -6.9589120,
                'longitude' => 110.4534110,
            ],
            [
                'code' => 'MDN',
                'name' => 'Medan Sumatera Hub',
                'type' => 'regional_hub',
                'city' => 'Medan',
                'address' => 'Jl. Sisingamangaraja KM 7, Medan',
                'phone' => '061-7865-3310',
                'latitude' => 3.5398120,
                'longitude' => 98.7042150,
            ],
            [
                'code' => 'MKS',
                'name' => 'Makassar Sulawesi Hub',
                'type' => 'regional_hub',
                'city' => 'Makassar',
                'address' => 'Kawasan Pergudangan KIMA Kav. 8, Makassar',
                'phone' => '0411-5122-770',
                'latitude' => -5.0987120,
                'longitude' => 119.5123450,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::query()->updateOrCreate(['code' => $branch['code']], $branch);
        }
    }
}
