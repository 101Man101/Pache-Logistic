<?php

declare(strict_types=1);

namespace App\Modules\FIN\Application\UseCases;

use App\Models\Customer;
use App\Models\Invoice;
use App\Modules\FIN\Domain\Repositories\InvoiceRepository;
use App\Modules\FIN\Domain\Services\InvoiceTotalsCalculator;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use DomainException;

/**
 * Manual Invoice (AR) Creation for non-AWB services, charter, WMS storage, or custom billing.
 */
final readonly class CreateManualInvoiceUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private InvoiceRepository $invoices,
        private InvoiceTotalsCalculator $calculator,
    ) {}

    /**
     * @param  array{
     *     customer_id: int,
     *     period_start: string,
     *     period_end: string,
     *     due_date: string,
     *     apply_ppn: bool,
     *     apply_pph23: bool,
     *     notes?: string|null,
     *     lines: list<array{description: string, quantity: float|int|string, unit?: string|null, unit_price: float|int|string}>,
     * }  $payload
     */
    public function execute(array $payload, ?int $actorId = null): Invoice
    {
        return $this->transactions->run(function () use ($payload, $actorId): Invoice {
            /** @var Customer $customer */
            $customer = Customer::query()->lockForUpdate()->findOrFail($payload['customer_id']);

            if (! $customer->is_active) {
                throw new DomainException("Pelanggan {$customer->name} berstatus nonaktif.");
            }

            $totals = $this->calculator->calculate(
                $payload['lines'],
                applyPpn: (bool) $payload['apply_ppn'],
                applyPph23: (bool) $payload['apply_pph23'],
            );

            $prefix = 'INV-PCH-'.now()->format('Ym').'-';
            $seq = $this->invoices->nextSequence($prefix);
            $invoiceNumber = $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            return $this->invoices->createManualWithLines([
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'manual',
                'customer_id' => $customer->id,
                'period_start' => $payload['period_start'],
                'period_end' => $payload['period_end'],
                'apply_ppn' => (bool) $payload['apply_ppn'],
                'apply_pph23' => (bool) $payload['apply_pph23'],
                'subtotal' => $totals['subtotal'],
                'ppn_amount' => $totals['ppn_amount'],
                'pph23_amount' => $totals['pph23_amount'],
                'total_amount' => $totals['total_amount'],
                'due_date' => $payload['due_date'],
                'notes' => isset($payload['notes']) && trim((string) $payload['notes']) !== ''
                    ? trim((string) $payload['notes'])
                    : null,
                'status' => 'unpaid',
                'created_by' => $actorId,
            ], $totals['lines']);
        });
    }
}
