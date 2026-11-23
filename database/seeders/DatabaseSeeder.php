<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            VendorSeeder::class,
            SurchargeSeeder::class,
            TariffSeeder::class,
            ShipmentSeeder::class,
            InvoiceSeeder::class,
            WarehouseSeeder::class,
            TransportSeeder::class,
        ]);
    }
}
