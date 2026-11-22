<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\FinancialLedger;
use App\Models\InventoryLedger;
use App\Models\Invoice;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StorageBin;
use App\Models\Tariff;
use App\Models\TrackingLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Multi-Branch Hierarchy (MDM)
        $branchesData = [
            ['code' => 'JKT', 'name' => 'Jakarta Pusat Head Office', 'type' => 'head_office', 'city' => 'Jakarta', 'address' => 'Jl. Jend. Sudirman Kav. 52, Jakarta Selatan', 'phone' => '021-5501-8800', 'latitude' => -6.2249120, 'longitude' => 106.8091240],
            ['code' => 'BDG', 'name' => 'Bandung Regional Hub', 'type' => 'regional_hub', 'city' => 'Bandung', 'address' => 'Jl. Soekarno Hatta No. 412, Bandung', 'phone' => '022-7510-2210', 'latitude' => -6.9412890, 'longitude' => 107.6387420],
            ['code' => 'SBY', 'name' => 'Surabaya Regional Hub', 'type' => 'regional_hub', 'city' => 'Surabaya', 'address' => 'Kawasan Industri Rungkut Blok C-14, Surabaya', 'phone' => '031-8432-9900', 'latitude' => -7.3264510, 'longitude' => 112.7618820],
            ['code' => 'SMG', 'name' => 'Semarang Sorting Center', 'type' => 'sorting_center', 'city' => 'Semarang', 'address' => 'Jl. Kaligawe Raya KM 5, Semarang', 'phone' => '024-6582-4110', 'latitude' => -6.9589120, 'longitude' => 110.4534110],
            ['code' => 'MDN', 'name' => 'Medan Sumatera Hub', 'type' => 'regional_hub', 'city' => 'Medan', 'address' => 'Jl. Sisingamangaraja KM 7, Medan', 'phone' => '061-7865-3310', 'latitude' => 3.5398120, 'longitude' => 98.7042150],
            ['code' => 'MKS', 'name' => 'Makassar Sulawesi Hub', 'type' => 'regional_hub', 'city' => 'Makassar', 'address' => 'Kawasan Pergudangan KIMA Kav. 8, Makassar', 'phone' => '0411-5122-770', 'latitude' => -5.0987120, 'longitude' => 119.5123450],
        ];

        $branches = [];
        foreach ($branchesData as $b) {
            $branches[$b['code']] = Branch::query()->updateOrCreate(['code' => $b['code']], $b);
        }

        // 2. Demo Users across Personas
        $usersData = [
            ['name' => 'Raka Pradipta', 'email' => 'admin@pachelogistic.id', 'role' => 'super_admin', 'branch_id' => $branches['JKT']->id],
            ['name' => 'Nadia Kusuma', 'email' => 'oms@pachelogistic.id', 'role' => 'admin_oms', 'branch_id' => $branches['JKT']->id],
            ['name' => 'Dimas Santoso', 'email' => 'kasir@pachelogistic.id', 'role' => 'finance_cashier', 'branch_id' => $branches['JKT']->id],
            ['name' => 'Bagus Wijaya', 'email' => 'wms@pachelogistic.id', 'role' => 'operator_wms', 'branch_id' => $branches['JKT']->id],
            ['name' => 'Arif Hidayat', 'email' => 'tms@pachelogistic.id', 'role' => 'dispatcher_tms', 'branch_id' => $branches['SBY']->id],
        ];

        $users = [];
        foreach ($usersData as $u) {
            $users[$u['email']] = User::query()->updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'branch_id' => $u['branch_id'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
        $adminId = $users['admin@pachelogistic.id']->id;

        // 3. Customers & B2B TOP Contracts
        $customersData = [
            ['code' => 'CUST-B2B-001', 'name' => 'PT Nusantara Medika Farma', 'type' => 'b2b_contract', 'npwp' => '01.425.889.2-012.000', 'phone' => '021-5201-9910', 'email' => 'ap@nusantaramedika.co.id', 'address' => 'Kawasan Industri Pulogadung Lot 12, Jakarta Timur', 'is_top_enabled' => true, 'top_days' => 30, 'credit_limit' => 150000000],
            ['code' => 'CUST-B2B-002', 'name' => 'PT Sinar Baja Industri', 'type' => 'b2b_contract', 'npwp' => '02.811.230.9-614.000', 'phone' => '031-8912-4421', 'email' => 'logistics@sinarbaja.co.id', 'address' => 'Jl. Raya Margomulyo No. 88, Surabaya', 'is_top_enabled' => true, 'top_days' => 14, 'credit_limit' => 85000000],
            ['code' => 'CUST-B2B-003', 'name' => 'CV Karya Elektronik Sejahtera', 'type' => 'b2b_contract', 'npwp' => '03.112.904.4-422.000', 'phone' => '022-4231-5502', 'email' => 'finance@karyaelektronik.id', 'address' => 'Jl. Asia Afrika No. 119, Bandung', 'is_top_enabled' => true, 'top_days' => 7, 'credit_limit' => 45000000],
            ['code' => 'CUST-B2B-004', 'name' => 'PT Agro Prima Distribusi', 'type' => 'b2b_contract', 'npwp' => '01.998.120.7-121.000', 'phone' => '061-4510-8831', 'email' => 'supply@agroprima.co.id', 'address' => 'Jl. Krakatau Ujung No. 201, Medan', 'is_top_enabled' => true, 'top_days' => 30, 'credit_limit' => 120000000],
            ['code' => 'CUST-RTL-001', 'name' => 'Atelier Tekstil Kemang', 'type' => 'retail', 'npwp' => null, 'phone' => '0812-8891-4420', 'email' => 'order@atelierkemang.id', 'address' => 'Jl. Kemang Raya No. 45, Jakarta Selatan', 'is_top_enabled' => false, 'top_days' => 0, 'credit_limit' => 0],
            ['code' => 'CUST-RTL-002', 'name' => 'Kopi Roastery Dago', 'type' => 'retail', 'npwp' => null, 'phone' => '0813-2244-7718', 'email' => 'hello@kopidago.id', 'address' => 'Jl. Ir. H. Juanda No. 210, Bandung', 'is_top_enabled' => false, 'top_days' => 0, 'credit_limit' => 0],
        ];

        $customers = [];
        foreach ($customersData as $c) {
            $customers[$c['code']] = Customer::query()->updateOrCreate(['code' => $c['code']], $c);
        }

        // 4. Rate Engine Matrix (No COD Fee — Strictly Non-COD)
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

        foreach ($routeRates as [$orig, $dest, $regRate, $expRate, $crgRate, $regSla, $expSla, $crgSla]) {
            foreach ([
                ['REG', 6000, 1, $regRate, $regSla],
                ['EXP', 5000, 1, $expRate, $expSla],
                ['CRG', 4000, 10, $crgRate, $crgSla],
            ] as [$svc, $div, $minW, $rate, $sla]) {
                Tariff::query()->updateOrCreate(
                    [
                        'origin_branch_id' => $branches[$orig]->id,
                        'destination_branch_id' => $branches[$dest]->id,
                        'service_type' => $svc,
                    ],
                    [
                        'volumetric_divisor' => $div,
                        'min_weight_kg' => $minW,
                        'rate_per_kg' => $rate,
                        'insurance_rate_pct' => 0.25,
                        'sla_days' => $sla,
                        'is_active' => true,
                    ]
                );
            }
        }

        // 5. Realistic Non-COD Shipments (Online Payment, Cash Loket, B2B TOP)
        if (Shipment::query()->count() === 0) {
            $shipmentsSeed = [
                [
                    'awb' => 'PCH-JKT-261121-00001',
                    'ref' => 'PO-MED-8821',
                    'cust' => 'CUST-B2B-001',
                    'orig' => 'JKT',
                    'dest' => 'SBY',
                    'consignee' => 'RS Graha Husada Surabaya',
                    'consignee_phone' => '081234567890',
                    'consignee_addr' => 'Jl. Dharmahusada Indah No. 18, Surabaya',
                    'svc' => 'EXP',
                    'div' => 5000,
                    'act' => 24.5,
                    'vol' => 28.0,
                    'chg' => 28.0,
                    'rate' => 28000,
                    'ins' => 45000,
                    'pm' => PaymentMethod::TOP,
                    'ps' => PaymentStatus::BILLED,
                    'status' => ShipmentStatus::DELIVERED,
                    'days_ago' => 4,
                    'epod' => ['Dr. Hendra Wijaya', 'Resepsionis Logistik', -7.2698210, 112.7712340],
                ],
                [
                    'awb' => 'PCH-JKT-261121-00002',
                    'ref' => 'PO-MED-8840',
                    'cust' => 'CUST-B2B-001',
                    'orig' => 'JKT',
                    'dest' => 'BDG',
                    'consignee' => 'Klinik Utama Pasteur',
                    'consignee_phone' => '081398765432',
                    'consignee_addr' => 'Jl. Dr. Djunjunan No. 102, Bandung',
                    'svc' => 'REG',
                    'div' => 6000,
                    'act' => 15.0,
                    'vol' => 12.5,
                    'chg' => 15.0,
                    'rate' => 9000,
                    'ins' => 18000,
                    'pm' => PaymentMethod::TOP,
                    'ps' => PaymentStatus::BILLED,
                    'status' => ShipmentStatus::DELIVERED,
                    'days_ago' => 3,
                    'epod' => ['Siti Rahmawati', 'Yang Bersangkutan', -6.8931220, 107.5892310],
                ],
                [
                    'awb' => 'PCH-JKT-261121-00003',
                    'ref' => 'ORD-ATK-901',
                    'cust' => 'CUST-RTL-001',
                    'orig' => 'JKT',
                    'dest' => 'SBY',
                    'consignee' => 'Butik Seruni Tunjungan',
                    'consignee_phone' => '081122334455',
                    'consignee_addr' => 'Jl. Basuki Rahmat No. 74, Surabaya',
                    'svc' => 'REG',
                    'div' => 6000,
                    'act' => 8.2,
                    'vol' => 11.4,
                    'chg' => 12.0,
                    'rate' => 15000,
                    'ins' => 12500,
                    'pm' => PaymentMethod::ONLINE,
                    'ps' => PaymentStatus::PAID,
                    'status' => ShipmentStatus::IN_TRANSIT,
                    'days_ago' => 1,
                    'epod' => null,
                ],
                [
                    'awb' => 'PCH-JKT-261121-00004',
                    'ref' => 'ORD-ATK-904',
                    'cust' => 'CUST-RTL-001',
                    'orig' => 'JKT',
                    'dest' => 'MDN',
                    'consignee' => 'Galeri Tenun Deli',
                    'consignee_phone' => '081566778899',
                    'consignee_addr' => 'Jl. Imam Bonjol No. 32, Medan',
                    'svc' => 'EXP',
                    'div' => 5000,
                    'act' => 6.0,
                    'vol' => 5.2,
                    'chg' => 6.0,
                    'rate' => 42000,
                    'ins' => 15000,
                    'pm' => PaymentMethod::CASH,
                    'ps' => PaymentStatus::PAID,
                    'status' => ShipmentStatus::OUT_FOR_DELIVERY,
                    'days_ago' => 1,
                    'epod' => null,
                ],
                [
                    'awb' => 'PCH-SBY-261121-00005',
                    'ref' => 'SBI-CRG-310',
                    'cust' => 'CUST-B2B-002',
                    'orig' => 'SBY',
                    'dest' => 'JKT',
                    'consignee' => 'Proyek Menara Sudirman',
                    'consignee_phone' => '081788990011',
                    'consignee_addr' => 'Jl. Jend. Sudirman Kav. 28, Jakarta',
                    'svc' => 'CRG',
                    'div' => 4000,
                    'act' => 145.0,
                    'vol' => 160.0,
                    'chg' => 160.0,
                    'rate' => 9500,
                    'ins' => 95000,
                    'pm' => PaymentMethod::TOP,
                    'ps' => PaymentStatus::BILLED,
                    'status' => ShipmentStatus::DELIVERED,
                    'days_ago' => 2,
                    'epod' => ['Ir. Bambang Suryo', 'Kepala Gudang Proyek', -6.2154120, 106.8201230],
                ],
                [
                    'awb' => 'PCH-BDG-261121-00006',
                    'ref' => 'KOP-DAGO-44',
                    'cust' => 'CUST-RTL-002',
                    'orig' => 'BDG',
                    'dest' => 'JKT',
                    'consignee' => 'Kedai Kopi Senopati',
                    'consignee_phone' => '081900112233',
                    'consignee_addr' => 'Jl. Senopati No. 89, Jakarta Selatan',
                    'svc' => 'REG',
                    'div' => 6000,
                    'act' => 18.0,
                    'vol' => 19.2,
                    'chg' => 20.0,
                    'rate' => 9000,
                    'ins' => 0,
                    'pm' => PaymentMethod::ONLINE,
                    'ps' => PaymentStatus::PENDING,
                    'status' => ShipmentStatus::BOOKED,
                    'days_ago' => 0,
                    'epod' => null,
                ],
                [
                    'awb' => 'PCH-JKT-261121-00007',
                    'ref' => 'ORD-CASH-112',
                    'cust' => 'CUST-RTL-001',
                    'orig' => 'JKT',
                    'dest' => 'SMG',
                    'consignee' => 'Toko Kain Pekojan',
                    'consignee_phone' => '081244556677',
                    'consignee_addr' => 'Jl. Pekojan No. 14, Semarang',
                    'svc' => 'REG',
                    'div' => 6000,
                    'act' => 10.5,
                    'vol' => 9.0,
                    'chg' => 11.0,
                    'rate' => 12000,
                    'ins' => 0,
                    'pm' => PaymentMethod::CASH,
                    'ps' => PaymentStatus::PENDING,
                    'status' => ShipmentStatus::BOOKED,
                    'days_ago' => 0,
                    'epod' => null,
                ],
                [
                    'awb' => 'PCH-JKT-261121-00008',
                    'ref' => 'KEL-INV-771',
                    'cust' => 'CUST-B2B-003',
                    'orig' => 'JKT',
                    'dest' => 'SBY',
                    'consignee' => 'Sentra Komputer Hi-Tech',
                    'consignee_phone' => '081355667788',
                    'consignee_addr' => 'Jl. Kusuma Bangsa No. 116, Surabaya',
                    'svc' => 'EXP',
                    'div' => 5000,
                    'act' => 32.0,
                    'vol' => 38.4,
                    'chg' => 39.0,
                    'rate' => 28000,
                    'ins' => 65000,
                    'pm' => PaymentMethod::TOP,
                    'ps' => PaymentStatus::BILLED,
                    'status' => ShipmentStatus::ORIGIN_HUB,
                    'days_ago' => 0,
                    'epod' => null,
                ],
                [
                    'awb' => 'PCH-JKT-261121-00009',
                    'ref' => 'AGR-MKS-512',
                    'cust' => 'CUST-B2B-004',
                    'orig' => 'JKT',
                    'dest' => 'MKS',
                    'consignee' => 'Gudang Tani Nusantara Makassar',
                    'consignee_phone' => '081677889900',
                    'consignee_addr' => 'Jl. Perintis Kemerdekaan KM 12, Makassar',
                    'svc' => 'CRG',
                    'div' => 4000,
                    'act' => 210.0,
                    'vol' => 195.0,
                    'chg' => 210.0,
                    'rate' => 17500,
                    'ins' => 120000,
                    'pm' => PaymentMethod::TOP,
                    'ps' => PaymentStatus::BILLED,
                    'status' => ShipmentStatus::DESTINATION_HUB,
                    'days_ago' => 2,
                    'epod' => null,
                ],
                [
                    'awb' => 'PCH-JKT-261121-00010',
                    'ref' => 'QRIS-PAY-808',
                    'cust' => 'CUST-RTL-002',
                    'orig' => 'JKT',
                    'dest' => 'SBY',
                    'consignee' => 'Roastery Gubeng',
                    'consignee_phone' => '081812349876',
                    'consignee_addr' => 'Jl. Sulawesi No. 41, Surabaya',
                    'svc' => 'REG',
                    'div' => 6000,
                    'act' => 14.0,
                    'vol' => 16.0,
                    'chg' => 16.0,
                    'rate' => 15000,
                    'ins' => 10000,
                    'pm' => PaymentMethod::ONLINE,
                    'ps' => PaymentStatus::PAID,
                    'status' => ShipmentStatus::ORIGIN_HUB,
                    'days_ago' => 0,
                    'epod' => null,
                ],
            ];

            $createdShipments = [];
            foreach ($shipmentsSeed as $idx => $s) {
                $cust = $customers[$s['cust']];
                $orig = $branches[$s['orig']];
                $dest = $branches[$s['dest']];
                $shipCost = $s['chg'] * $s['rate'];
                $total = $shipCost + $s['ins'];
                $createdAt = now()->subDays($s['days_ago'])->subHours(4);

                $shipment = Shipment::query()->create([
                    'awb_number' => $s['awb'],
                    'reference_no' => $s['ref'],
                    'customer_id' => $cust->id,
                    'origin_branch_id' => $orig->id,
                    'destination_branch_id' => $dest->id,
                    'shipper_name' => $cust->name,
                    'shipper_phone' => $cust->phone,
                    'shipper_address' => $cust->address,
                    'consignee_name' => $s['consignee'],
                    'consignee_phone' => $s['consignee_phone'],
                    'consignee_address' => $s['consignee_addr'],
                    'service_type' => $s['svc'],
                    'volumetric_divisor' => $s['div'],
                    'actual_weight_kg' => $s['act'],
                    'volumetric_weight_kg' => $s['vol'],
                    'chargeable_weight_kg' => $s['chg'],
                    'shipping_cost' => $shipCost,
                    'insurance_cost' => $s['ins'],
                    'total_amount' => $total,
                    'payment_method' => $s['pm'],
                    'payment_status' => $s['ps'],
                    'payment_reference' => match ($s['pm']) {
                        PaymentMethod::ONLINE => 'VA-PCH-8810'.str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT),
                        PaymentMethod::CASH => $s['ps'] === PaymentStatus::PAID ? 'RCPT-'.$s['awb'] : null,
                        PaymentMethod::TOP => 'TOP-NET'.$cust->top_days.'-'.$cust->code,
                    },
                    'paid_at' => $s['ps'] === PaymentStatus::PAID ? $createdAt->copy()->addMinutes(5) : null,
                    'status' => $s['status'],
                    'delivered_at' => $s['epod'] !== null ? $createdAt->copy()->addHours(26) : null,
                    'epod_receiver_name' => $s['epod'][0] ?? null,
                    'epod_receiver_relation' => $s['epod'][1] ?? null,
                    'epod_latitude' => $s['epod'][2] ?? null,
                    'epod_longitude' => $s['epod'][3] ?? null,
                    'epod_notes' => $s['epod'] !== null ? 'Diterima lengkap dalam kondisi segel utuh (Non-COD).' : null,
                    'created_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $shipment->items()->create([
                    'description' => "Koli Utama {$s['ref']}",
                    'qty' => 2,
                    'actual_weight_kg' => round($s['act'] / 2, 2),
                    'length_cm' => 45,
                    'width_cm' => 35,
                    'height_cm' => 30,
                ]);

                // Tracking timeline
                TrackingLog::query()->create([
                    'shipment_id' => $shipment->id,
                    'branch_id' => $orig->id,
                    'status' => ShipmentStatus::BOOKED->value,
                    'location' => "{$orig->name} ({$orig->city})",
                    'description' => "Resi diterbitkan (Non-COD · {$s['pm']->label()}).",
                    'latitude' => $orig->latitude,
                    'longitude' => $orig->longitude,
                    'actor_id' => $adminId,
                    'created_at' => $createdAt,
                ]);

                if (in_array($s['status'], [ShipmentStatus::ORIGIN_HUB, ShipmentStatus::IN_TRANSIT, ShipmentStatus::DESTINATION_HUB, ShipmentStatus::OUT_FOR_DELIVERY, ShipmentStatus::DELIVERED], true)) {
                    TrackingLog::query()->create([
                        'shipment_id' => $shipment->id,
                        'branch_id' => $orig->id,
                        'status' => ShipmentStatus::ORIGIN_HUB->value,
                        'location' => "{$orig->name} — Sorting Hub",
                        'description' => 'Verifikasi timbang ulang selesai & lolos Non-COD Dispatch Gate (BR-08).',
                        'latitude' => $orig->latitude,
                        'longitude' => $orig->longitude,
                        'actor_id' => $adminId,
                        'created_at' => $createdAt->copy()->addHours(3),
                    ]);
                }

                if (in_array($s['status'], [ShipmentStatus::IN_TRANSIT, ShipmentStatus::DESTINATION_HUB, ShipmentStatus::OUT_FOR_DELIVERY, ShipmentStatus::DELIVERED], true)) {
                    TrackingLog::query()->create([
                        'shipment_id' => $shipment->id,
                        'branch_id' => $orig->id,
                        'status' => ShipmentStatus::IN_TRANSIT->value,
                        'location' => "Linehaul {$orig->city} → {$dest->city}",
                        'description' => "Armada linehaul berangkat menuju {$dest->name}.",
                        'actor_id' => $adminId,
                        'created_at' => $createdAt->copy()->addHours(7),
                    ]);
                }

                if (in_array($s['status'], [ShipmentStatus::DESTINATION_HUB, ShipmentStatus::OUT_FOR_DELIVERY, ShipmentStatus::DELIVERED], true)) {
                    TrackingLog::query()->create([
                        'shipment_id' => $shipment->id,
                        'branch_id' => $dest->id,
                        'status' => ShipmentStatus::DESTINATION_HUB->value,
                        'location' => "{$dest->name} ({$dest->city})",
                        'description' => 'Tiba di Hub tujuan dan selesai inbound unloading.',
                        'latitude' => $dest->latitude,
                        'longitude' => $dest->longitude,
                        'actor_id' => $adminId,
                        'created_at' => $createdAt->copy()->addHours(19),
                    ]);
                }

                if (in_array($s['status'], [ShipmentStatus::OUT_FOR_DELIVERY, ShipmentStatus::DELIVERED], true)) {
                    TrackingLog::query()->create([
                        'shipment_id' => $shipment->id,
                        'branch_id' => $dest->id,
                        'status' => ShipmentStatus::OUT_FOR_DELIVERY->value,
                        'location' => "{$dest->city} — Kurir Last-Mile",
                        'description' => 'Sedang diantar kurir Pache Logistic (Non-COD, tanpa penagihan tunai).',
                        'actor_id' => $adminId,
                        'created_at' => $createdAt->copy()->addHours(22),
                    ]);
                }

                if ($s['epod'] !== null) {
                    TrackingLog::query()->create([
                        'shipment_id' => $shipment->id,
                        'branch_id' => $dest->id,
                        'status' => ShipmentStatus::DELIVERED->value,
                        'location' => "{$dest->city} (GPS {$s['epod'][2]}, {$s['epod'][3]})",
                        'description' => "Diterima oleh {$s['epod'][0]} ({$s['epod'][1]}) — e-POD terverifikasi.",
                        'latitude' => $s['epod'][2],
                        'longitude' => $s['epod'][3],
                        'actor_id' => $adminId,
                        'created_at' => $createdAt->copy()->addHours(26),
                    ]);
                }

                if ($s['ps'] === PaymentStatus::PAID) {
                    FinancialLedger::query()->create([
                        'entry_code' => 'FIN-SEED-'.str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT),
                        'branch_id' => $orig->id,
                        'channel' => $s['pm']->value,
                        'direction' => 'credit',
                        'amount' => $total,
                        'reference_type' => 'shipment',
                        'reference_id' => $shipment->id,
                        'description' => "Pelunasan {$s['pm']->label()} — {$shipment->awb_number}",
                        'actor_id' => $adminId,
                        'created_at' => $createdAt->copy()->addMinutes(5),
                    ]);
                }

                $createdShipments[$s['awb']] = $shipment;
            }

            // Sample B2B Invoice (already billed for PCH-JKT-261121-00001; 00002 & 00005 remain ready for Auto-Invoicing demo!)
            $billedShipment = $createdShipments['PCH-JKT-261121-00001'];
            $sub = (float) $billedShipment->total_amount;
            $ppn = round($sub * 0.11, 2);
            $pph = round($sub * 0.02, 2);
            $inv = Invoice::query()->create([
                'invoice_number' => 'INV-PCH-202611-0001',
                'customer_id' => $billedShipment->customer_id,
                'period_start' => now()->subDays(14)->toDateString(),
                'period_end' => now()->subDays(3)->toDateString(),
                'subtotal' => $sub,
                'ppn_amount' => $ppn,
                'pph23_amount' => $pph,
                'total_amount' => round($sub + $ppn - $pph, 2),
                'due_date' => now()->addDays(26)->toDateString(),
                'status' => 'unpaid',
                'created_by' => $adminId,
            ]);
            $inv->lines()->create(['shipment_id' => $billedShipment->id, 'amount' => $sub]);
        }

        // 6. WMS Warehouses, Bins, Products & Immutable Ledger
        $whJkt = Warehouse::query()->updateOrCreate(
            ['code' => 'WH-JKT-01'],
            ['branch_id' => $branches['JKT']->id, 'name' => 'Gudang Fulfillment Jakarta Pusat', 'zone_type' => 'dry', 'is_active' => true]
        );
        $whSby = Warehouse::query()->updateOrCreate(
            ['code' => 'WH-SBY-01'],
            ['branch_id' => $branches['SBY']->id, 'name' => 'Cold Storage & Hub Surabaya', 'zone_type' => 'cold', 'is_active' => true]
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
            ['customer_id' => $customers['CUST-B2B-001']->id, 'name' => 'Infusion Set Steril 500ml (Box 20)', 'allocation_rule' => 'FEFO', 'unit' => 'box']
        );
        $prodMed2 = Product::query()->updateOrCreate(
            ['sku' => 'SKU-MED-204'],
            ['customer_id' => $customers['CUST-B2B-001']->id, 'name' => 'Reagen Diagnostik Cold-Chain', 'allocation_rule' => 'FEFO', 'unit' => 'pack']
        );
        $prodElk1 = Product::query()->updateOrCreate(
            ['sku' => 'SKU-ELK-305'],
            ['customer_id' => $customers['CUST-B2B-003']->id, 'name' => 'Router Industrial Dual-SIM 5G', 'allocation_rule' => 'FIFO', 'unit' => 'unit']
        );
        Product::query()->updateOrCreate(
            ['sku' => 'SKU-BJA-410'],
            ['customer_id' => $customers['CUST-B2B-002']->id, 'name' => 'Bolt Flange Galvanis M16 (Karton)', 'allocation_rule' => 'FIFO', 'unit' => 'karton']
        );

        if (InventoryLedger::query()->count() === 0) {
            InventoryLedger::query()->create([
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
            ]);
            InventoryLedger::query()->create([
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
            ]);
            InventoryLedger::query()->create([
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
                'created_at' => now()->subDays(1),
            ]);
            InventoryLedger::query()->create([
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
            ]);
        }

        // 7. TMS Vehicles, Drivers & Manifests (including 1 expiring soon & 1 expired for BR-06 demo!)
        $vehicles = [
            'B 9124 PXH' => Vehicle::query()->updateOrCreate(['plate_number' => 'B 9124 PXH'], [
                'branch_id' => $branches['JKT']->id, 'type' => 'CDD Box 6-Roda', 'capacity_kg' => 4500, 'capacity_cbm' => 16.5,
                'stnk_expires_at' => now()->addMonths(9)->toDateString(), 'kir_expires_at' => now()->addMonths(4)->toDateString(), 'status' => 'available',
            ]),
            'B 9482 PXC' => Vehicle::query()->updateOrCreate(['plate_number' => 'B 9482 PXC'], [
                'branch_id' => $branches['JKT']->id, 'type' => 'Fuso Wingbox', 'capacity_kg' => 8500, 'capacity_cbm' => 38.0,
                'stnk_expires_at' => now()->addDays(18)->toDateString(), 'kir_expires_at' => now()->addMonths(5)->toDateString(), 'status' => 'on_trip',
            ]),
            'L 8821 PXS' => Vehicle::query()->updateOrCreate(['plate_number' => 'L 8821 PXS'], [
                'branch_id' => $branches['SBY']->id, 'type' => 'Engkel Box', 'capacity_kg' => 2500, 'capacity_cbm' => 10.0,
                'stnk_expires_at' => now()->addMonths(7)->toDateString(), 'kir_expires_at' => now()->subDays(4)->toDateString(), 'status' => 'maintenance',
            ]),
        ];

        $drivers = [
            'SIM-B2-001' => Driver::query()->updateOrCreate(['sim_number' => 'SIM-B2-001'], [
                'branch_id' => $branches['JKT']->id, 'name' => 'Slamet Riyadi', 'phone' => '0812-3311-9010', 'sim_expires_at' => now()->addYears(2)->toDateString(), 'status' => 'available',
            ]),
            'SIM-B2-002' => Driver::query()->updateOrCreate(['sim_number' => 'SIM-B2-002'], [
                'branch_id' => $branches['JKT']->id, 'name' => 'Dedi Kurniawan', 'phone' => '0813-4422-8812', 'sim_expires_at' => now()->addDays(22)->toDateString(), 'status' => 'on_trip',
            ]),
            'SIM-B1-003' => Driver::query()->updateOrCreate(['sim_number' => 'SIM-B1-003'], [
                'branch_id' => $branches['SBY']->id, 'name' => 'Agus Setiawan', 'phone' => '0815-6677-1120', 'sim_expires_at' => now()->addMonths(14)->toDateString(), 'status' => 'available',
            ]),
        ];

        if (Manifest::query()->count() === 0) {
            $openManifest = Manifest::query()->create([
                'manifest_number' => 'MNF-JKT-SBY-261121-001',
                'origin_branch_id' => $branches['JKT']->id,
                'destination_branch_id' => $branches['SBY']->id,
                'vehicle_id' => $vehicles['B 9124 PXH']->id,
                'driver_id' => $drivers['SIM-B2-001']->id,
                'seal_number' => 'SEAL-PCH882',
                'driver_advance_amount' => 1850000,
                'status' => 'open',
                'created_by' => $adminId,
            ]);

            $transitManifest = Manifest::query()->create([
                'manifest_number' => 'MNF-JKT-SBY-261120-004',
                'origin_branch_id' => $branches['JKT']->id,
                'destination_branch_id' => $branches['SBY']->id,
                'vehicle_id' => $vehicles['B 9482 PXC']->id,
                'driver_id' => $drivers['SIM-B2-002']->id,
                'seal_number' => 'SEAL-PCH741',
                'driver_advance_amount' => 2400000,
                'status' => 'departed',
                'departed_at' => now()->subHours(11),
                'created_by' => $adminId,
            ]);

            $s3 = Shipment::query()->where('awb_number', 'PCH-JKT-261121-00003')->first();
            $s8 = Shipment::query()->where('awb_number', 'PCH-JKT-261121-00008')->first();
            if ($s3 !== null) {
                ManifestItem::query()->create(['manifest_id' => $transitManifest->id, 'shipment_id' => $s3->id, 'scanned_at' => now()->subHours(12)]);
            }
            if ($s8 !== null) {
                ManifestItem::query()->create(['manifest_id' => $openManifest->id, 'shipment_id' => $s8->id, 'scanned_at' => now()->subHours(1)]);
            }
        }
    }
}
