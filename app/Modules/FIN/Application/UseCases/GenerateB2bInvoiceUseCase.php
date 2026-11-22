<?php

declare(strict_types=1);

namespace App\Modules\FIN\Application\UseCases;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Automated B2B TOP Invoicing (FR-FIN-03):
 * Aggregates unbilled DELIVERED TOP shipments, computes PPN 11% & PPh 23 2%.
 */
final readonly class GenerateB2bInvoiceUseCase
{
    public function execute(int $customerId, ?int $actorId = null): Invoice
    {
        return DB::transaction(function () use ($customerId, $actorId): Invoice {
            /** @var Customer $customer */
            $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);

            if (! $customer->is_top_enabled) {
                throw new DomainException("Pelanggan {$customer->name} bukan pelanggan kontrak TOP B2B.");
            }

            $shipments = Shipment::query()
                ->where('customer_id', $customer->id)
                ->where('payment_method', PaymentMethod::TOP->value)
                ->where('status', ShipmentStatus::DELIVERED->value)
                ->whereDoesntHave('invoiceLine')
                ->lockForUpdate()
                ->get();

            if ($shipments->isEmpty()) {
                throw new DomainException("Tidak ada resi TOP berstatus Delivered yang belum ditagih untuk {$customer->name}.");
            }

            $subtotal = (float) $shipments->sum('total_amount');
            $ppnAmount = round($subtotal * 0.11, 2);
            $pph23Amount = round($subtotal * 0.02, 2);
            $totalAmount = round($subtotal + $ppnAmount - $pph23Amount, 2);

            $prefix = 'INV-PCH-'.now()->format('Ym').'-';
            $lastInvoice = Invoice::query()
                ->where('invoice_number', 'like', "{$prefix}%")
                ->orderByDesc('invoice_number')
                ->lockForUpdate()
                ->value('invoice_number');
            $seq = is_string($lastInvoice) ? ((int) substr($lastInvoice, -4) + 1) : 1;
            $invoiceNumber = $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            /** @var Invoice $invoice */
            $invoice = Invoice::query()->create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customer->id,
                'period_start' => $shipments->min('created_at')?->toDateString() ?? now()->startOfMonth()->toDateString(),
                'period_end' => now()->toDateString(),
                'subtotal' => $subtotal,
                'ppn_amount' => $ppnAmount,
                'pph23_amount' => $pph23Amount,
                'total_amount' => $totalAmount,
                'due_date' => now()->addDays(max(7, $customer->top_days))->toDateString(),
                'status' => 'unpaid',
                'created_by' => $actorId,
            ]);

            foreach ($shipments as $shipment) {
                $invoice->lines()->create([
                    'shipment_id' => $shipment->id,
                    'amount' => $shipment->total_amount,
                ]);
            }

            return $invoice->load(['customer', 'lines.shipment']);
        });
    }
}
