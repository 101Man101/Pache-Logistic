<?php

declare(strict_types=1);

namespace App\Modules\FIN\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\VendorBill;
use App\Modules\FIN\Application\UseCases\CreateManualInvoiceUseCase;
use App\Modules\FIN\Application\UseCases\CreateVendorBillUseCase;
use App\Modules\FIN\Application\UseCases\GenerateB2bInvoiceUseCase;
use App\Modules\FIN\Application\UseCases\MarkInvoicePaidUseCase;
use App\Modules\FIN\Application\UseCases\MarkVendorBillPaidUseCase;
use App\Modules\FIN\Infrastructure\Persistence\FinanceSummaryQueryService;
use App\Modules\FIN\Presentation\Http\Requests\GenerateInvoiceRequest;
use App\Modules\FIN\Presentation\Http\Requests\MarkDocumentPaidRequest;
use App\Modules\FIN\Presentation\Http\Requests\StoreManualInvoiceRequest;
use App\Modules\FIN\Presentation\Http\Requests\StoreVendorBillRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
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

    public function storeManualInvoice(StoreManualInvoiceRequest $request, CreateManualInvoiceUseCase $useCase): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $invoice = $useCase->execute([
                'customer_id' => (int) $validated['customer_id'],
                'period_start' => (string) $validated['period_start'],
                'period_end' => (string) $validated['period_end'],
                'due_date' => (string) $validated['due_date'],
                'apply_ppn' => (bool) ($validated['apply_ppn'] ?? false),
                'apply_pph23' => (bool) ($validated['apply_pph23'] ?? false),
                'notes' => isset($validated['notes']) ? (string) $validated['notes'] : null,
                'lines' => array_values($validated['lines']),
            ], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', "Invoice Manual {$invoice->invoice_number} berhasil diterbitkan.");
    }

    public function showInvoice(Invoice $invoice, FinanceSummaryQueryService $queryService): Response
    {
        return Inertia::render('finance/InvoiceShow', $queryService->buildInvoiceDetail($invoice));
    }

    public function printInvoice(Invoice $invoice, FinanceSummaryQueryService $queryService): Response
    {
        return Inertia::render('finance/InvoicePrint', $queryService->buildInvoiceDetail($invoice));
    }

    public function markPaid(MarkDocumentPaidRequest $request, Invoice $invoice, MarkInvoicePaidUseCase $useCase): RedirectResponse
    {
        $ref = $request->validated('payment_reference');
        $useCase->execute($invoice, $request->user()?->id, is_string($ref) ? $ref : null);

        return back()->with('success', "Invoice {$invoice->invoice_number} ditandai LUNAS dan dicatat ke Buku Besar Keuangan.");
    }

    public function storeVendorBill(StoreVendorBillRequest $request, CreateVendorBillUseCase $useCase): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $bill = $useCase->execute([
                'vendor_id' => (int) $validated['vendor_id'],
                'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
                'manifest_id' => isset($validated['manifest_id']) ? (int) $validated['manifest_id'] : null,
                'vendor_invoice_no' => isset($validated['vendor_invoice_no']) ? (string) $validated['vendor_invoice_no'] : null,
                'bill_date' => (string) $validated['bill_date'],
                'due_date' => (string) $validated['due_date'],
                'apply_ppn' => (bool) ($validated['apply_ppn'] ?? false),
                'apply_pph23' => (bool) ($validated['apply_pph23'] ?? false),
                'notes' => isset($validated['notes']) ? (string) $validated['notes'] : null,
                'lines' => array_values($validated['lines']),
            ], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('finance.vendor-bills.show', $bill)
            ->with('success', "Vendor Bill {$bill->bill_number} berhasil dicatat ke Hutang Usaha (AP).");
    }

    public function showVendorBill(VendorBill $vendorBill, FinanceSummaryQueryService $queryService): Response
    {
        return Inertia::render('finance/VendorBillShow', $queryService->buildVendorBillDetail($vendorBill));
    }

    public function printVendorBill(VendorBill $vendorBill, FinanceSummaryQueryService $queryService): Response
    {
        return Inertia::render('finance/VendorBillPrint', $queryService->buildVendorBillDetail($vendorBill));
    }

    public function markVendorBillPaid(
        MarkDocumentPaidRequest $request,
        VendorBill $vendorBill,
        MarkVendorBillPaidUseCase $useCase,
    ): RedirectResponse {
        $ref = $request->validated('payment_reference');
        $useCase->execute($vendorBill, $request->user()?->id, is_string($ref) ? $ref : null);

        return back()->with('success', "Vendor Bill {$vendorBill->bill_number} ditandai DIBAYAR dan dicatat sebagai debit di Buku Besar.");
    }
}
