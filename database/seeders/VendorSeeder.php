<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'code' => 'VND-AIR-001',
                'name' => 'PT Garuda Cargo Express',
                'type' => 'air_freight',
                'contact_person' => 'Hendra Gunawan',
                'phone' => '021-5509-1120',
                'email' => 'ops@garudacargo-partner.id',
                'address' => 'Area Kargo Bandara Soekarno-Hatta, Tangerang',
                'payment_terms_days' => 14,
                'base_cost_per_kg' => 18000,
                'notes' => 'Mitra SMU udara jalur JKT–SBY, JKT–MDN, JKT–MKS.',
                'is_active' => true,
            ],
            [
                'code' => 'VND-LHL-001',
                'name' => 'PT Mitra Trucking Nusantara',
                'type' => 'linehaul',
                'contact_person' => 'Budi Santoso',
                'phone' => '021-8890-4412',
                'email' => 'dispatch@mitratrucking.id',
                'address' => 'Jl. Raya Cakung Cilincing KM 4, Jakarta Utara',
                'payment_terms_days' => 30,
                'base_cost_per_kg' => 4500,
                'notes' => 'Vendor wingbox & CDD Jawa–Sumatera.',
                'is_active' => true,
            ],
            [
                'code' => 'VND-SEA-001',
                'name' => 'PT Meratus Linehaul Laut',
                'type' => 'sea_freight',
                'contact_person' => 'Yusuf Pratama',
                'phone' => '031-3291-7780',
                'email' => 'cargo@meratus-partner.id',
                'address' => 'Pelabuhan Tanjung Perak Dermaga Jamrud, Surabaya',
                'payment_terms_days' => 30,
                'base_cost_per_kg' => 6000,
                'notes' => 'Kontainer kargo laut SBY–MKS dan lintas pulau.',
                'is_active' => true,
            ],
            [
                'code' => 'VND-LML-001',
                'name' => 'PT Kurir Ekspres Mandiri',
                'type' => 'last_mile',
                'contact_person' => 'Rina Kartika',
                'phone' => '022-7312-9044',
                'email' => 'cs@kurirekspres.id',
                'address' => 'Jl. Bypass Soekarno Hatta No. 180, Bandung',
                'payment_terms_days' => 7,
                'base_cost_per_kg' => 3500,
                'notes' => 'Mitra last-mile area satelit & daerah terpencil (ODA).',
                'is_active' => true,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::query()->updateOrCreate(['code' => $vendor['code']], $vendor);
        }
    }
}
