<?php

declare(strict_types=1);

namespace App\Modules\FIN\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FinancialLedger;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Modules\FIN\Application\UseCases\GenerateB2bInvoiceUseCase;
use App\Modules\FIN\Application\UseCases\MarkInvoicePaidUseCase;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class FinanceController extends Controller
{
    public function index(): Response
    {
        $cashShipments = Shipment::query()
            ->with(['customer:id,name', 'originBranch:id,code,city'])
            ->where('payment_method', PaymentMethod::CASH->value)
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (Shipment $s): array => [
                'id' => $s->id,
                'awb_number' => $s->awb_number,
                'customer_name' => $s->customer->name ?? '-',
                'branch' => $s->originBranch->code ?? '-',
                'total_amount' => (float) $s->total_amount,
                'payment_status' => $s->payment_status->value,
                'payment_reference' => $s->payment_reference,
                'paid_at' => $s->paid_at?->format('d M Y, H:i'),
                'created_at' => $s->created_at?->format('d M Y, H:i'),
            ]);

        $onlineShipments = Shipment::query()
            ->with(['customer:id,name', 'originBranch:id,code,city'])
            ->where('payment_method', PaymentMethod::ONLINE->value)
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (Shipment $s): array => [
                'id' => $s->id,
                'awb_number' => $s->awb_number,
                'customer_name' => $s->customer->name ?? '-',
                'branch' => $s->originBranch->code ?? '-',
                'total_amount' => (float) $s->total_amount,
                'payment_status' => $s->payment_status->value,
                'payment_reference' => $s->payment_reference,
                'paid_at' => $s->paid_at?->format('d M Y, H:i'),
                'created_at' => $s->created_at?->format('d M Y, H:i'),
            ]);

        $topCustomers = Customer::query()
            ->where('is_top_enabled', true)
            ->orderBy('name')
            ->get()
            ->map(static function (Customer $c): array {
                $unbilledDeliveredCount = Shipment::query()
                    ->where('customer_id', $c->id)
                    ->where('payment_method', PaymentMethod::TOP->value)
                    ->where('status', ShipmentStatus::DELIVERED->value)
                    ->whereDoesntHave('invoiceLine')
                    ->count();

                $unbilledDeliveredAmount = (float) Shipment::query()
                    ->where('customer_id', $c->id)
                    ->where('payment_method', PaymentMethod::TOP->value)
                    ->where('status', ShipmentStatus::DELIVERED->value)
                    ->whereDoesntHave('invoiceLine')
                    ->sum('total_amount');

                return [
                    'id' => $c->id,
                    'code' => $c->code,
                    'name' => $c->name,
                    'top_days' => $c->top_days,
                    'credit_limit' => (float) $c->credit_limit,
                    'credit_exposure' => $c->currentCreditExposure(),
                    'unbilled_delivered_count' => $unbilledDeliveredCount,
                    'unbilled_delivered_amount' => $unbilledDeliveredAmount,
                ];
            });

        $invoices = Invoice::query()
            ->with(['customer:id,name,code,top_days', 'lines'])
            ->orderByDesc('created_at')
            ->get()
            ->map(static function (Invoice $inv): array {
                $daysOverdue = $inv->status !== 'paid' && $inv->due_date->isPast()
                    ? (int) $inv->due_date->diffInDays(now())
                    : 0;

                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'customer_name' => $inv->customer->name ?? '-',
                    'period' => $inv->period_start->format('d M').' – '.$inv->period_end->format('d M Y'),
                    'shipment_count' => $inv->lines->count(),
                    'subtotal' => (float) $inv->subtotal,
                    'ppn_amount' => (float) $inv->ppn_amount,
                    'pph23_amount' => (float) $inv->pph23_amount,
                    'total_amount' => (float) $inv->total_amount,
                    'due_date' => $inv->due_date->format('d M Y'),
                    'days_overdue' => $daysOverdue,
                    'aging_bucket' => match (true) {
                        $inv->status === 'paid' => 'Lunas',
                        $daysOverdue === 0 => 'Current',
                        $daysOverdue <= 30 => '1–30 hari',
                        $daysOverdue <= 60 => '31–60 hari',
                        default => '> 60 hari',
                    },
                    'status' => $inv->status,
                    'paid_at' => $inv->paid_at?->format('d M Y, H:i'),
                ];
            });

        $ledger = FinancialLedger::query()
            ->with('branch:id,code')
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(static fn (FinancialLedger $row): array => [
                'id' => $row->id,
                'entry_code' => $row->entry_code,
                'branch_code' => $row->branch->code ?? 'HO',
                'channel' => $row->channel,
                'direction' => $row->direction,
                'amount' => (float) $row->amount,
                'description' => $row->description,
                'created_at' => $row->created_at->format('d M Y, H:i'),
            ]);

        return Inertia::render('finance/Index', [
            'summary' => [
                'cash_drawer_paid' => (float) Shipment::query()->where('payment_method', PaymentMethod::CASH->value)->where('payment_status', PaymentStatus::PAID->value)->sum('total_amount'),
                'online_settled' => (float) Shipment::query()->where('payment_method', PaymentMethod::ONLINE->value)->where('payment_status', PaymentStatus::PAID->value)->sum('total_amount'),
                'top_ar_unpaid' => (float) Invoice::query()->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount'),
                'pending_gate_count' => Shipment::query()->where('payment_status', PaymentStatus::PENDING->value)->count(),
            ],
            'cashShipments' => $cashShipments,
            'onlineShipments' => $onlineShipments,
            'topCustomers' => $topCustomers,
            'invoices' => $invoices,
            'ledger' => $ledger,
        ]);
    }

    public function generateInvoice(Request $request, GenerateB2bInvoiceUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ]);

        try {
            $invoice = $useCase->execute((int) $validated['customer_id'], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil diterbitkan (termasuk PPN 11% & PPh 23 2%).");
    }

    public function markPaid(Request $request, Invoice $invoice, MarkInvoicePaidUseCase $useCase): RedirectResponse
    {
        $useCase->execute($invoice, $request->user()?->id);

        return back()->with('success', "Invoice {$invoice->invoice_number} ditandai LUNAS dan dicatat ke Buku Besar Keuangan.");
    }
}
