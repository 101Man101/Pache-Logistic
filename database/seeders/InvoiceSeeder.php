<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@pachelogistic.id')->value('id');
        $actorId = is_int($adminId) ? $adminId : null;

        if (Invoice::query()->count() === 0) {
            // Sample B2B Invoice for PCH-JKT-261121-00001; 00002 & 00005 remain unbilled for Auto-Invoicing demo
            $billedShipment = Shipment::query()
                ->with(['originBranch:id,code', 'destinationBranch:id,code'])
                ->where('awb_number', 'PCH-JKT-261121-00001')
                ->first();

            if ($billedShipment !== null) {
                $sub = (float) $billedShipment->total_amount;
                $ppn = round($sub * 0.11, 2);
                $pph = round($sub * 0.02, 2);

                $invoice = Invoice::query()->create([
                    'invoice_number' => 'INV-PCH-202611-0001',
                    'invoice_type' => 'b2b_top',
                    'customer_id' => $billedShipment->customer_id,
                    'period_start' => now()->subDays(14)->toDateString(),
                    'period_end' => now()->subDays(3)->toDateString(),
                    'apply_ppn' => true,
                    'apply_pph23' => true,
                    'subtotal' => $sub,
                    'ppn_amount' => $ppn,
                    'pph23_amount' => $pph,
                    'total_amount' => round($sub + $ppn - $pph, 2),
                    'due_date' => now()->addDays(26)->toDateString(),
                    'status' => 'unpaid',
                    'created_by' => $actorId,
                ]);

                $origin = $billedShipment->originBranch->code ?? 'JKT';
                $dest = $billedShipment->destinationBranch->code ?? 'SBY';

                $invoice->lines()->create([
                    'shipment_id' => $billedShipment->id,
                    'description' => "Pengiriman {$billedShipment->awb_number} ({$origin} → {$dest} · {$billedShipment->service_type})",
                    'quantity' => 1,
                    'unit' => 'resi',
                    'unit_price' => $sub,
                    'amount' => $sub,
                ]);
            }
        }

        if (VendorBill::query()->count() === 0) {
            $vendor = Vendor::query()->where('code', 'VND-LHL-001')->first();
            $jkt = Branch::query()->where('code', 'JKT')->first();

            if ($vendor !== null) {
                $subtotal = 1850000.0;
                $ppn = round($subtotal * 0.11, 2);
                $pph = round($subtotal * 0.02, 2);

                $bill = VendorBill::query()->create([
                    'bill_number' => 'AP-PCH-202611-0001',
                    'vendor_invoice_no' => 'MTN-INV-2611-084',
                    'vendor_id' => $vendor->id,
                    'branch_id' => $jkt?->id,
                    'bill_date' => now()->subDays(4)->toDateString(),
                    'due_date' => now()->addDays(26)->toDateString(),
                    'subtotal' => $subtotal,
                    'apply_ppn' => true,
                    'apply_pph23' => true,
                    'ppn_amount' => $ppn,
                    'pph23_amount' => $pph,
                    'total_amount' => round($subtotal + $ppn - $pph, 2),
                    'status' => 'unpaid',
                    'notes' => 'Tagihan sewa linehaul wingbox JKT → SBY periode November.',
                    'created_by' => $actorId,
                ]);

                $bill->lines()->create([
                    'description' => 'Charter Linehaul Wingbox JKT → SBY (Manifest Mingguan)',
                    'quantity' => 1,
                    'unit' => 'trip',
                    'unit_price' => $subtotal,
                    'amount' => $subtotal,
                ]);
            }
        }
    }
}
