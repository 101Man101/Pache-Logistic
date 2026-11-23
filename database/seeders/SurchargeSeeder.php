<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Surcharge;
use Illuminate\Database\Seeder;

class SurchargeSeeder extends Seeder
{
    public function run(): void
    {
        $surcharges = [
            [
                'code' => 'FSC',
                'name' => 'Fuel / BBM Surcharge',
                'calc_mode' => 'percentage',
                'amount' => 5,
                'description' => 'Penyesuaian fluktuasi bahan bakar armada linehaul & udara (5% dari ongkir dasar).',
                'is_active' => true,
            ],
            [
                'code' => 'PACK-KAYU',
                'name' => 'Packing Kayu & Paletisasi',
                'calc_mode' => 'fixed',
                'amount' => 75000,
                'description' => 'Proteksi rangka kayu & strapping palet standar kargo.',
                'is_active' => true,
            ],
            [
                'code' => 'HEAVY-HND',
                'name' => 'Heavy Cargo Handling (> 50 kg)',
                'calc_mode' => 'per_kg',
                'amount' => 2500,
                'description' => 'Biaya forklift / tenaga bongkar muat koli berat per kg.',
                'is_active' => true,
            ],
            [
                'code' => 'ODA',
                'name' => 'Out-of-Delivery Area (ODA)',
                'calc_mode' => 'fixed',
                'amount' => 50000,
                'description' => 'Tambahan jangkauan kecamatan luar zona hub utama.',
                'is_active' => true,
            ],
            [
                'code' => 'FRAGILE',
                'name' => 'Cold / Fragile Special Handling',
                'calc_mode' => 'fixed',
                'amount' => 25000,
                'description' => 'Penanganan khusus barang medis, elektronik sensitif, dan pecah belah.',
                'is_active' => true,
            ],
        ];

        foreach ($surcharges as $surcharge) {
            Surcharge::query()->updateOrCreate(['code' => $surcharge['code']], $surcharge);
        }
    }
}
