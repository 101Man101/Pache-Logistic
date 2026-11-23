<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryLedger;
use App\Models\Product;
use App\Models\StorageBin;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->get()->keyBy('code');
        $customers = Customer::query()->get()->keyBy('code');

        $jkt = $branches->get('JKT');
        $sby = $branches->get('SBY');
        $custMed = $customers->get('CUST-B2B-001');
        $custBja = $customers->get('CUST-B2B-002');
        $custElk = $customers->get('CUST-B2B-003');

        if ($jkt === null || $sby === null || $custMed === null || $custBja === null || $custElk === null) {
            return;
        }

        $whJkt = Warehouse::query()->updateOrCreate(
            ['code' => 'WH-JKT-01'],
            ['branch_id' => $jkt->id, 'name' => 'Gudang Fulfillment Jakarta Pusat', 'zone_type' => 'dry', 'is_active' => true]
        );
        $whSby = Warehouse::query()->updateOrCreate(
            ['code' => 'WH-SBY-01'],
            ['branch_id' => $sby->id, 'name' => 'Cold Storage & Hub Surabaya', 'zone_type' => 'cold', 'is_active' => true]
        );

        $binDry1 = StorageBin::query()->updateOrCreate(
            ['code' => 'DRY-A1-R1-B01'],
            ['warehouse_id' => $whJkt->id, 'zone' => 'DRY', 'aisle' => 'A1', 'rack' => 'R1', 'bin' => 'B01', 'max_capacity' => 600]
        );
        $binDry2 = StorageBin::query()->updateOrCreate(
            ['code' => 'DRY-A1-R1-B02'],
            ['warehouse_id' => $whJkt->id, 'zone' => 'DRY', 'aisle' => 'A1', 'rack' => 'R1', 'bin' => 'B02', 'max_capacity' => 600]
        );
        StorageBin::query()->updateOrCreate(
            ['code' => 'DRY-A2-R2-B01'],
            ['warehouse_id' => $whJkt->id, 'zone' => 'DRY', 'aisle' => 'A2', 'rack' => 'R2', 'bin' => 'B01', 'max_capacity' => 450]
        );
        $binCold1 = StorageBin::query()->updateOrCreate(
            ['code' => 'CLD-C1-R1-B01'],
            ['warehouse_id' => $whSby->id, 'zone' => 'COLD', 'aisle' => 'C1', 'rack' => 'R1', 'bin' => 'B01', 'max_capacity' => 300]
        );

        $prodMed1 = Product::query()->updateOrCreate(
            ['sku' => 'SKU-MED-101'],
            ['customer_id' => $custMed->id, 'name' => 'Infusion Set Steril 500ml (Box 20)', 'allocation_rule' => 'FEFO', 'unit' => 'box']
        );
        $prodMed2 = Product::query()->updateOrCreate(
            ['sku' => 'SKU-MED-204'],
            ['customer_id' => $custMed->id, 'name' => 'Reagen Diagnostik Cold-Chain', 'allocation_rule' => 'FEFO', 'unit' => 'pack']
        );
        $prodElk1 = Product::query()->updateOrCreate(
            ['sku' => 'SKU-ELK-305'],
            ['customer_id' => $custElk->id, 'name' => 'Router Industrial Dual-SIM 5G', 'allocation_rule' => 'FIFO', 'unit' => 'unit']
        );
        Product::query()->updateOrCreate(
            ['sku' => 'SKU-BJA-410'],
            ['customer_id' => $custBja->id, 'name' => 'Bolt Flange Galvanis M16 (Karton)', 'allocation_rule' => 'FIFO', 'unit' => 'karton']
        );

        if (InventoryLedger::query()->count() > 0) {
            return;
        }

        $adminId = User::query()->where('email', 'admin@pachelogistic.id')->value('id');
        $adminId = is_int($adminId) ? $adminId : null;

        $ledgerEntries = [
            [
                'warehouse_id' => $whJkt->id,
                'storage_bin_id' => $binDry1->id,
                'product_id' => $prodMed1->id,
                'movement_type' => 'inbound',
                'reference_code' => 'ASN-2611-001',
                'quantity_change' => 240,
                'balance_after' => 240,
                'batch_number' => 'BATCH-MED-26A',
                'expires_at' => now()->addMonths(8)->toDateString(),
                'reason' => 'Inbound Receiving PO Nusantara Medika',
                'actor_id' => $adminId,
                'created_at' => now()->subDays(2),
            ],
            [
                'warehouse_id' => $whJkt->id,
                'storage_bin_id' => $binDry1->id,
                'product_id' => $prodMed1->id,
                'movement_type' => 'outbound',
                'reference_code' => 'PCH-JKT-261121-00001',
                'quantity_change' => -40,
                'balance_after' => 200,
                'batch_number' => 'BATCH-MED-26A',
                'expires_at' => now()->addMonths(8)->toDateString(),
                'reason' => 'Wave Picking FEFO untuk RS Graha Husada',
                'actor_id' => $adminId,
                'created_at' => now()->subDay(),
            ],
            [
                'warehouse_id' => $whSby->id,
                'storage_bin_id' => $binCold1->id,
                'product_id' => $prodMed2->id,
                'movement_type' => 'inbound',
                'reference_code' => 'ASN-2611-009',
                'quantity_change' => 120,
                'balance_after' => 120,
                'batch_number' => 'BATCH-CLD-26C',
                'expires_at' => now()->addDays(45)->toDateString(),
                'reason' => 'Inbound Cold-Chain Surabaya',
                'actor_id' => $adminId,
                'created_at' => now()->subDay(),
            ],
            [
                'warehouse_id' => $whJkt->id,
                'storage_bin_id' => $binDry2->id,
                'product_id' => $prodElk1->id,
                'movement_type' => 'inbound',
                'reference_code' => 'ASN-2611-014',
                'quantity_change' => 85,
                'balance_after' => 85,
                'batch_number' => 'LOT-ELK-09',
                'expires_at' => null,
                'reason' => 'Inbound Karya Elektronik',
                'actor_id' => $adminId,
                'created_at' => now()->subHours(12),
            ],
        ];

        foreach ($ledgerEntries as $entry) {
            InventoryLedger::query()->create($entry);
        }
    }
}
