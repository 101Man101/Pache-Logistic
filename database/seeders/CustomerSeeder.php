<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'code' => 'CUST-B2B-001',
                'name' => 'PT Nusantara Medika Farma',
                'type' => 'b2b_contract',
                'npwp' => '01.425.889.2-012.000',
                'phone' => '021-5201-9910',
                'email' => 'ap@nusantaramedika.co.id',
                'address' => 'Kawasan Industri Pulogadung Lot 12, Jakarta Timur',
                'is_top_enabled' => true,
                'top_days' => 30,
                'credit_limit' => 150000000,
            ],
            [
                'code' => 'CUST-B2B-002',
                'name' => 'PT Sinar Baja Industri',
                'type' => 'b2b_contract',
                'npwp' => '02.811.230.9-614.000',
                'phone' => '031-8912-4421',
                'email' => 'logistics@sinarbaja.co.id',
                'address' => 'Jl. Raya Margomulyo No. 88, Surabaya',
                'is_top_enabled' => true,
                'top_days' => 14,
                'credit_limit' => 85000000,
            ],
            [
                'code' => 'CUST-B2B-003',
                'name' => 'CV Karya Elektronik Sejahtera',
                'type' => 'b2b_contract',
                'npwp' => '03.112.904.4-422.000',
                'phone' => '022-4231-5502',
                'email' => 'finance@karyaelektronik.id',
                'address' => 'Jl. Asia Afrika No. 119, Bandung',
                'is_top_enabled' => true,
                'top_days' => 7,
                'credit_limit' => 45000000,
            ],
            [
                'code' => 'CUST-B2B-004',
                'name' => 'PT Agro Prima Distribusi',
                'type' => 'b2b_contract',
                'npwp' => '01.998.120.7-121.000',
                'phone' => '061-4510-8831',
                'email' => 'supply@agroprima.co.id',
                'address' => 'Jl. Krakatau Ujung No. 201, Medan',
                'is_top_enabled' => true,
                'top_days' => 30,
                'credit_limit' => 120000000,
            ],
            [
                'code' => 'CUST-RTL-001',
                'name' => 'Atelier Tekstil Kemang',
                'type' => 'retail',
                'npwp' => null,
                'phone' => '0812-8891-4420',
                'email' => 'order@atelierkemang.id',
                'address' => 'Jl. Kemang Raya No. 45, Jakarta Selatan',
                'is_top_enabled' => false,
                'top_days' => 0,
                'credit_limit' => 0,
            ],
            [
                'code' => 'CUST-RTL-002',
                'name' => 'Kopi Roastery Dago',
                'type' => 'retail',
                'npwp' => null,
                'phone' => '0813-2244-7718',
                'email' => 'hello@kopidago.id',
                'address' => 'Jl. Ir. H. Juanda No. 210, Bandung',
                'is_top_enabled' => false,
                'top_days' => 0,
                'credit_limit' => 0,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(['code' => $customer['code']], $customer);
        }
    }
}
