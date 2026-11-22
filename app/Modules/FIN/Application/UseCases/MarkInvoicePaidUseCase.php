<?php

declare(strict_types=1);

namespace App\Modules\FIN\Application\UseCases;

use App\Models\FinancialLedger;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

final readonly class MarkInvoicePaidUseCase
{
    public function execute(Invoice $invoice, ?int $actorId = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $actorId): Invoice {
            /** @var Invoice $locked */
            $locked = Invoice::query()->with('customer')->lockForUpdate()->findOrFail($invoice->id);

            if ($locked->status === 'paid') {
                return $locked;
            }

            $locked->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            FinancialLedger::query()->create([
                'entry_code' => 'FIN-INV-'.now()->format('ymdHis').'-'.str_pad((string) $locked->id, 4, '0', STR_PAD_LEFT),
                'branch_id' => null,
                'channel' => 'top_invoice',
                'direction' => 'credit',
                'amount' => $locked->total_amount,
                'reference_type' => 'invoice',
                'reference_id' => $locked->id,
                'description' => "Pelunasan Invoice B2B TOP {$locked->invoice_number} ({$locked->customer?->name})",
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            return $locked->refresh();
        });
    }
}
