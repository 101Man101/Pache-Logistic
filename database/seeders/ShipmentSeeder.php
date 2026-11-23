<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\FinancialLedger;
use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Models\User;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        if (Shipment::query()->count() > 0) {
            return;
        }

        $branches = Branch::query()->get()->keyBy('code');
        $customers = Customer::query()->get()->keyBy('code');
        $adminId = User::query()->where('email', 'admin@pachelogistic.id')->value('id');
        $adminId = is_int($adminId) ? $adminId : null;

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

        foreach ($shipmentsSeed as $idx => $s) {
            $cust = $customers->get($s['cust']);
            $orig = $branches->get($s['orig']);
            $dest = $branches->get($s['dest']);
            if ($cust === null || $orig === null || $dest === null) {
                continue;
            }

            $shipCost = $s['chg'] * $s['rate'];
            $total = $shipCost + $s['ins'];
            $createdAt = now()->subDays($s['days_ago'])->subHours(4);
            $epod = $s['epod'];

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
                'delivered_at' => $epod !== null ? $createdAt->copy()->addHours(26) : null,
                'epod_receiver_name' => $epod[0] ?? null,
                'epod_receiver_relation' => $epod[1] ?? null,
                'epod_latitude' => $epod[2] ?? null,
                'epod_longitude' => $epod[3] ?? null,
                'epod_notes' => $epod !== null ? 'Diterima lengkap dalam kondisi segel utuh (Non-COD).' : null,
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

            $this->seedTrackingTimeline($shipment, $orig, $dest, $s['pm'], $s['status'], $epod, $adminId, $createdAt);

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
        }
    }

    /**
     * @param  array{0: string, 1: string, 2: float, 3: float}|null  $epod
     */
    private function seedTrackingTimeline(
        Shipment $shipment,
        Branch $orig,
        Branch $dest,
        PaymentMethod $paymentMethod,
        ShipmentStatus $status,
        ?array $epod,
        ?int $actorId,
        CarbonImmutable $createdAt,
    ): void {
        TrackingLog::query()->create([
            'shipment_id' => $shipment->id,
            'branch_id' => $orig->id,
            'status' => ShipmentStatus::BOOKED->value,
            'location' => "{$orig->name} ({$orig->city})",
            'description' => "Resi diterbitkan (Non-COD · {$paymentMethod->label()}).",
            'latitude' => $orig->latitude,
            'longitude' => $orig->longitude,
            'actor_id' => $actorId,
            'created_at' => $createdAt,
        ]);

        $stagesAfterBooked = [
            ShipmentStatus::ORIGIN_HUB,
            ShipmentStatus::IN_TRANSIT,
            ShipmentStatus::DESTINATION_HUB,
            ShipmentStatus::OUT_FOR_DELIVERY,
            ShipmentStatus::DELIVERED,
        ];

        if (in_array($status, $stagesAfterBooked, true)) {
            TrackingLog::query()->create([
                'shipment_id' => $shipment->id,
                'branch_id' => $orig->id,
                'status' => ShipmentStatus::ORIGIN_HUB->value,
                'location' => "{$orig->name} — Sorting Hub",
                'description' => 'Verifikasi timbang ulang selesai & lolos Non-COD Dispatch Gate (BR-08).',
                'latitude' => $orig->latitude,
                'longitude' => $orig->longitude,
                'actor_id' => $actorId,
                'created_at' => $createdAt->copy()->addHours(3),
            ]);
        }

        if (in_array($status, array_slice($stagesAfterBooked, 1), true)) {
            TrackingLog::query()->create([
                'shipment_id' => $shipment->id,
                'branch_id' => $orig->id,
                'status' => ShipmentStatus::IN_TRANSIT->value,
                'location' => "Linehaul {$orig->city} → {$dest->city}",
                'description' => "Armada linehaul berangkat menuju {$dest->name}.",
                'actor_id' => $actorId,
                'created_at' => $createdAt->copy()->addHours(7),
            ]);
        }

        if (in_array($status, array_slice($stagesAfterBooked, 2), true)) {
            TrackingLog::query()->create([
                'shipment_id' => $shipment->id,
                'branch_id' => $dest->id,
                'status' => ShipmentStatus::DESTINATION_HUB->value,
                'location' => "{$dest->name} ({$dest->city})",
                'description' => 'Tiba di Hub tujuan dan selesai inbound unloading.',
                'latitude' => $dest->latitude,
                'longitude' => $dest->longitude,
                'actor_id' => $actorId,
                'created_at' => $createdAt->copy()->addHours(19),
            ]);
        }

        if (in_array($status, array_slice($stagesAfterBooked, 3), true)) {
            TrackingLog::query()->create([
                'shipment_id' => $shipment->id,
                'branch_id' => $dest->id,
                'status' => ShipmentStatus::OUT_FOR_DELIVERY->value,
                'location' => "{$dest->city} — Kurir Last-Mile",
                'description' => 'Sedang diantar kurir Pache Logistic (Non-COD, tanpa penagihan tunai).',
                'actor_id' => $actorId,
                'created_at' => $createdAt->copy()->addHours(22),
            ]);
        }

        if ($epod !== null) {
            TrackingLog::query()->create([
                'shipment_id' => $shipment->id,
                'branch_id' => $dest->id,
                'status' => ShipmentStatus::DELIVERED->value,
                'location' => "{$dest->city} (GPS {$epod[2]}, {$epod[3]})",
                'description' => "Diterima oleh {$epod[0]} ({$epod[1]}) — e-POD terverifikasi.",
                'latitude' => $epod[2],
                'longitude' => $epod[3],
                'actor_id' => $actorId,
                'created_at' => $createdAt->copy()->addHours(26),
            ]);
        }
    }
}
