<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class TransportSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->get()->keyBy('code');
        $jkt = $branches->get('JKT');
        $sby = $branches->get('SBY');

        if ($jkt === null || $sby === null) {
            return;
        }

        // Vehicles (including 1 expiring soon & 1 expired for BR-06 demo)
        $vehicles = [
            'B 9124 PXH' => Vehicle::query()->updateOrCreate(['plate_number' => 'B 9124 PXH'], [
                'branch_id' => $jkt->id,
                'type' => 'CDD Box 6-Roda',
                'capacity_kg' => 4500,
                'capacity_cbm' => 16.5,
                'stnk_expires_at' => now()->addMonths(9)->toDateString(),
                'kir_expires_at' => now()->addMonths(4)->toDateString(),
                'status' => 'available',
            ]),
            'B 9482 PXC' => Vehicle::query()->updateOrCreate(['plate_number' => 'B 9482 PXC'], [
                'branch_id' => $jkt->id,
                'type' => 'Fuso Wingbox',
                'capacity_kg' => 8500,
                'capacity_cbm' => 38.0,
                'stnk_expires_at' => now()->addDays(18)->toDateString(),
                'kir_expires_at' => now()->addMonths(5)->toDateString(),
                'status' => 'on_trip',
            ]),
            'L 8821 PXS' => Vehicle::query()->updateOrCreate(['plate_number' => 'L 8821 PXS'], [
                'branch_id' => $sby->id,
                'type' => 'Engkel Box',
                'capacity_kg' => 2500,
                'capacity_cbm' => 10.0,
                'stnk_expires_at' => now()->addMonths(7)->toDateString(),
                'kir_expires_at' => now()->subDays(4)->toDateString(),
                'status' => 'maintenance',
            ]),
        ];

        $drivers = [
            'SIM-B2-001' => Driver::query()->updateOrCreate(['sim_number' => 'SIM-B2-001'], [
                'branch_id' => $jkt->id,
                'name' => 'Slamet Riyadi',
                'phone' => '0812-3311-9010',
                'sim_expires_at' => now()->addYears(2)->toDateString(),
                'status' => 'available',
            ]),
            'SIM-B2-002' => Driver::query()->updateOrCreate(['sim_number' => 'SIM-B2-002'], [
                'branch_id' => $jkt->id,
                'name' => 'Dedi Kurniawan',
                'phone' => '0813-4422-8812',
                'sim_expires_at' => now()->addDays(22)->toDateString(),
                'status' => 'on_trip',
            ]),
            'SIM-B1-003' => Driver::query()->updateOrCreate(['sim_number' => 'SIM-B1-003'], [
                'branch_id' => $sby->id,
                'name' => 'Agus Setiawan',
                'phone' => '0815-6677-1120',
                'sim_expires_at' => now()->addMonths(14)->toDateString(),
                'status' => 'available',
            ]),
        ];

        if (Manifest::query()->count() > 0) {
            return;
        }

        $adminId = User::query()->where('email', 'admin@pachelogistic.id')->value('id');
        $adminId = is_int($adminId) ? $adminId : null;

        $openManifest = Manifest::query()->create([
            'manifest_number' => 'MNF-JKT-SBY-261121-001',
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'vehicle_id' => $vehicles['B 9124 PXH']->id,
            'driver_id' => $drivers['SIM-B2-001']->id,
            'seal_number' => 'SEAL-PCH882',
            'driver_advance_amount' => 1850000,
            'status' => 'open',
            'created_by' => $adminId,
        ]);

        $transitManifest = Manifest::query()->create([
            'manifest_number' => 'MNF-JKT-SBY-261120-004',
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'vehicle_id' => $vehicles['B 9482 PXC']->id,
            'driver_id' => $drivers['SIM-B2-002']->id,
            'seal_number' => 'SEAL-PCH741',
            'driver_advance_amount' => 2400000,
            'status' => 'departed',
            'departed_at' => now()->subHours(11),
            'created_by' => $adminId,
        ]);

        $s3 = Shipment::query()->where('awb_number', 'PCH-JKT-261121-00003')->first();
        if ($s3 !== null) {
            ManifestItem::query()->create([
                'manifest_id' => $transitManifest->id,
                'shipment_id' => $s3->id,
                'scanned_at' => now()->subHours(12),
            ]);
        }

        $s8 = Shipment::query()->where('awb_number', 'PCH-JKT-261121-00008')->first();
        if ($s8 !== null) {
            ManifestItem::query()->create([
                'manifest_id' => $openManifest->id,
                'shipment_id' => $s8->id,
                'scanned_at' => now()->subHour(),
            ]);
        }
    }
}
