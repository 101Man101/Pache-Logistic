<?php

declare(strict_types=1);

namespace App\Modules\FIN\Application\UseCases;

use App\Models\Invoice;
use App\Modules\FIN\Domain\Repositories\InvoiceRepository;
use App\Modules\Shared\Domain\Contracts\FinancialLedgerRecorder;
use App\Modules\Shared\Domain\Contracts\TransactionManager;

final readonly class MarkInvoicePaidUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private InvoiceRepository $invoices,
        private FinancialLedgerRecorder $ledgerRecorder,
    ) {}

    public function execute(Invoice $invoice, ?int $actorId = null): Invoice
    {
        return $this->transactions->run(function () use ($invoice, $actorId): Invoice {
            $locked = $this->invoices->findForUpdateOrFail($invoice->id);

            if ($locked->status === 'paid') {
                return $locked;
            }

            $locked->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->ledgerRecorder->recordCredit(
                prefix: 'FIN-INV',
                branchId: null,
                channel: 'top_invoice',
                amount: (float) $locked->total_amount,
                referenceType: 'invoice',
                referenceId: $locked->id,
                description: "Pelunasan Invoice B2B TOP {$locked->invoice_number} ({$locked->customer?->name})",
                actorId: $actorId,
            );

            return $locked->refresh();
        });
    }
}
