<?php

declare(strict_types=1);

namespace App\Modules\FIN\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Modules\FIN\Application\UseCases\GenerateB2bInvoiceUseCase;
use App\Modules\FIN\Application\UseCases\MarkInvoicePaidUseCase;
use App\Modules\FIN\Infrastructure\Persistence\FinanceSummaryQueryService;
use App\Modules\FIN\Presentation\Http\Requests\GenerateInvoiceRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class FinanceController extends Controller
{
    public function index(FinanceSummaryQueryService $queryService): Response
    {
        return Inertia::render('finance/Index', $queryService->buildIndexData());
    }

    public function generateInvoice(GenerateInvoiceRequest $request, GenerateB2bInvoiceUseCase $useCase): RedirectResponse
    {
        try {
            $invoice = $useCase->execute((int) $request->validated('customer_id'), $request->user()?->id);
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
