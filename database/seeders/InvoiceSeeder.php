<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        if (Invoice::query()->count() > 0) {
            return;
        }

        // Sample B2B Invoice for PCH-JKT-261121-00001; 00002 & 00005 remain unbilled for Auto-Invoicing demo
        $billedShipment = Shipment::query()->where('awb_number', 'PCH-JKT-261121-00001')->first();
        if ($billedShipment === null) {
            return;
        }

        $adminId = User::query()->where('email', 'admin@pachelogistic.id')->value('id');
        $sub = (float) $billedShipment->total_amount;
        $ppn = round($sub * 0.11, 2);
        $pph = round($sub * 0.02, 2);

        $invoice = Invoice::query()->create([
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
            'created_by' => is_int($adminId) ? $adminId : null,
        ]);

        $invoice->lines()->create([
            'shipment_id' => $billedShipment->id,
            'amount' => $sub,
        ]);
    }
}
