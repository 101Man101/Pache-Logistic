<?php

declare(strict_types=1);

namespace App\Modules\FIN\Application\UseCases;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Modules\FIN\Domain\Repositories\InvoiceRepository;
use App\Modules\FIN\Domain\Services\InvoiceTotalsCalculator;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use DomainException;

/**
 * Automated B2B TOP Invoicing (FR-FIN-03):
 * Aggregates unbilled DELIVERED TOP shipments, computes PPN 11% & PPh 23 2%.
 */
final readonly class GenerateB2bInvoiceUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private InvoiceRepository $invoices,
        private InvoiceTotalsCalculator $calculator,
    ) {}

    public function execute(int $customerId, ?int $actorId = null): Invoice
    {
        return $this->transactions->run(function () use ($customerId, $actorId): Invoice {
            /** @var Customer $customer */
            $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);

            if (! $customer->is_top_enabled) {
                throw new DomainException("Pelanggan {$customer->name} bukan pelanggan kontrak TOP B2B.");
            }

            $shipments = $this->invoices->unbilledDeliveredTopShipments($customer->id);
            if ($shipments->isEmpty()) {
                throw new DomainException("Tidak ada resi TOP berstatus Delivered yang belum ditagih untuk {$customer->name}.");
            }

            $items = array_values(
                $shipments->map(static fn (Shipment $s): array => [
                    'shipment_id' => $s->id,
                    'description' => $s->awb_number,
                    'quantity' => 1,
                    'unit' => 'resi',
                    'unit_price' => (float) $s->total_amount,
                ])->all(),
            );

            $totals = $this->calculator->calculate($items, applyPpn: true, applyPph23: true);

            $prefix = 'INV-PCH-'.now()->format('Ym').'-';
            $seq = $this->invoices->nextSequence($prefix);
            $invoiceNumber = $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            return $this->invoices->createWithLines([
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'b2b_top',
                'customer_id' => $customer->id,
                'period_start' => $shipments->min('created_at')?->toDateString() ?? now()->startOfMonth()->toDateString(),
                'period_end' => now()->toDateString(),
                'apply_ppn' => true,
                'apply_pph23' => true,
                'subtotal' => $totals['subtotal'],
                'ppn_amount' => $totals['ppn_amount'],
                'pph23_amount' => $totals['pph23_amount'],
                'total_amount' => $totals['total_amount'],
                'due_date' => now()->addDays(max(7, $customer->top_days))->toDateString(),
                'status' => 'unpaid',
                'created_by' => $actorId,
            ], $shipments);
        });
    }
}
