<?php

declare(strict_types=1);

namespace App\Modules\FIN\Infrastructure\Persistence;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\FinancialLedger;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorBillLine;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Carbon\CarbonInterface;

final readonly class FinanceSummaryQueryService
{
    public function __construct(
        private ShipmentRepository $shipments,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildIndexData(): array
    {
        return [
            'summary' => [
                'cash_drawer_paid' => (float) Shipment::query()->where('payment_method', PaymentMethod::CASH->value)->where('payment_status', PaymentStatus::PAID->value)->sum('total_amount'),
                'online_settled' => (float) Shipment::query()->where('payment_method', PaymentMethod::ONLINE->value)->where('payment_status', PaymentStatus::PAID->value)->sum('total_amount'),
                'top_ar_unpaid' => (float) Invoice::query()->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount'),
                'ap_unpaid' => (float) VendorBill::query()->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount'),
                'ap_unpaid_count' => VendorBill::query()->whereIn('status', ['unpaid', 'overdue'])->count(),
                'pending_gate_count' => Shipment::query()->where('payment_status', PaymentStatus::PENDING->value)->count(),
            ],
            'cashShipments' => $this->mapShipmentsByPaymentMethod(PaymentMethod::CASH),
            'onlineShipments' => $this->mapShipmentsByPaymentMethod(PaymentMethod::ONLINE),
            'topCustomers' => $this->mapTopCustomers(),
            'customers' => $this->mapActiveCustomers(),
            'vendors' => $this->mapActiveVendors(),
            'branches' => $this->mapBranches(),
            'manifests' => $this->mapVendorManifests(),
            'unbilledVendorShipments' => $this->mapUnbilledVendorShipments(),
            'invoices' => $this->mapInvoices(),
            'vendorBills' => $this->mapVendorBills(),
            'ledger' => $this->mapRecentLedger(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildInvoiceDetail(Invoice $invoice): array
    {
        $invoice->load(['customer', 'lines.shipment.originBranch', 'lines.shipment.destinationBranch']);
        $aging = $this->resolveAging($invoice->status, $invoice->due_date);

        return [
            'company' => $this->companyProfile(),
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_type' => $invoice->invoice_type,
                'invoice_type_label' => $invoice->invoice_type === 'manual' ? 'Invoice Manual' : 'B2B TOP Auto-Invoice',
                'period' => $invoice->period_start->format('d M Y').' – '.$invoice->period_end->format('d M Y'),
                'period_start' => $invoice->period_start->format('d M Y'),
                'period_end' => $invoice->period_end->format('d M Y'),
                'due_date' => $invoice->due_date->format('d M Y'),
                'days_overdue' => $aging['days_overdue'],
                'aging_bucket' => $aging['aging_bucket'],
                'apply_ppn' => (bool) $invoice->apply_ppn,
                'apply_pph23' => (bool) $invoice->apply_pph23,
                'subtotal' => (float) $invoice->subtotal,
                'ppn_amount' => (float) $invoice->ppn_amount,
                'pph23_amount' => (float) $invoice->pph23_amount,
                'total_amount' => (float) $invoice->total_amount,
                'notes' => $invoice->notes,
                'status' => $invoice->status,
                'paid_at' => $invoice->paid_at?->format('d M Y, H:i'),
                'payment_reference' => $invoice->payment_reference,
                'customer' => [
                    'id' => $invoice->customer->id ?? 0,
                    'code' => $invoice->customer->code ?? '-',
                    'name' => $invoice->customer->name ?? '-',
                    'npwp' => $invoice->customer->npwp,
                    'phone' => $invoice->customer->phone ?? '-',
                    'email' => $invoice->customer->email,
                    'address' => $invoice->customer->address ?? '-',
                    'is_top_enabled' => (bool) ($invoice->customer->is_top_enabled ?? false),
                    'top_days' => (int) ($invoice->customer->top_days ?? 14),
                ],
                'lines' => $invoice->lines->map(static function (InvoiceLine $line): array {
                    $shipment = $line->shipment;
                    $route = $shipment !== null
                        ? ($shipment->originBranch->code ?? '?').' → '.($shipment->destinationBranch->code ?? '?')
                        : null;

                    $desc = $line->description;
                    if (($desc === null || $desc === '') && $shipment !== null) {
                        $desc = "Pengiriman {$shipment->awb_number} ({$route} · {$shipment->service_type})";
                    }

                    return [
                        'id' => $line->id,
                        'shipment_id' => $line->shipment_id,
                        'awb_number' => $shipment?->awb_number,
                        'route' => $route,
                        'service_type' => $shipment?->service_type,
                        'chargeable_weight_kg' => $shipment !== null ? (float) $shipment->chargeable_weight_kg : null,
                        'description' => $desc ?? 'Layanan Logistik Pache',
                        'quantity' => (float) ($line->quantity ?: 1),
                        'unit' => $line->unit ?: 'resi',
                        'unit_price' => (float) ((float) $line->unit_price > 0 ? $line->unit_price : $line->amount),
                        'amount' => (float) $line->amount,
                    ];
                })->values()->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildVendorBillDetail(VendorBill $bill): array
    {
        $bill->load(['vendor', 'branch', 'manifest.originBranch', 'manifest.destinationBranch', 'lines.shipment']);
        $aging = $this->resolveAging($bill->status, $bill->due_date);

        return [
            'company' => $this->companyProfile(),
            'vendorBill' => [
                'id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'vendor_invoice_no' => $bill->vendor_invoice_no,
                'bill_date' => $bill->bill_date->format('d M Y'),
                'due_date' => $bill->due_date->format('d M Y'),
                'days_overdue' => $aging['days_overdue'],
                'aging_bucket' => $aging['aging_bucket'],
                'apply_ppn' => (bool) $bill->apply_ppn,
                'apply_pph23' => (bool) $bill->apply_pph23,
                'subtotal' => (float) $bill->subtotal,
                'ppn_amount' => (float) $bill->ppn_amount,
                'pph23_amount' => (float) $bill->pph23_amount,
                'total_amount' => (float) $bill->total_amount,
                'notes' => $bill->notes,
                'status' => $bill->status,
                'paid_at' => $bill->paid_at?->format('d M Y, H:i'),
                'payment_reference' => $bill->payment_reference,
                'branch' => $bill->branch !== null ? [
                    'code' => $bill->branch->code,
                    'name' => $bill->branch->name,
                    'city' => $bill->branch->city,
                ] : null,
                'manifest' => $bill->manifest !== null ? [
                    'id' => $bill->manifest->id,
                    'manifest_number' => $bill->manifest->manifest_number,
                    'route' => ($bill->manifest->originBranch->code ?? '?').' → '.($bill->manifest->destinationBranch->code ?? '?'),
                ] : null,
                'vendor' => [
                    'id' => $bill->vendor->id ?? 0,
                    'code' => $bill->vendor->code ?? '-',
                    'name' => $bill->vendor->name ?? '-',
                    'type_label' => $bill->vendor?->type->label() ?? '-',
                    'contact_person' => $bill->vendor?->contact_person,
                    'phone' => $bill->vendor->phone ?? '-',
                    'email' => $bill->vendor?->email,
                    'address' => $bill->vendor?->address,
                    'payment_terms_days' => (int) ($bill->vendor->payment_terms_days ?? 14),
                ],
                'lines' => $bill->lines->map(static fn (VendorBillLine $line): array => [
                    'id' => $line->id,
                    'shipment_id' => $line->shipment_id,
                    'awb_number' => $line->shipment?->awb_number,
                    'description' => $line->description,
                    'quantity' => (float) $line->quantity,
                    'unit' => $line->unit,
                    'unit_price' => (float) $line->unit_price,
                    'amount' => (float) $line->amount,
                ])->values()->all(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function companyProfile(): array
    {
        return [
            'name' => 'PT Pache Logistic Nusantara',
            'tagline' => 'Corporate Logistics, Express Freight & Supply Chain ERP',
            'address' => 'Jl. Jend. Sudirman Kav. 52–53, SCBD, Jakarta Selatan 12190',
            'phone' => '(021) 5088-9000',
            'email' => 'finance@pachelogistic.id',
            'npwp' => '01.928.441.2-011.000',
            'bank_account' => 'Bank BCA KCU Sudirman · No. Rek 500-889-2026 a.n. PT Pache Logistic Nusantara',
        ];
    }

    /**
     * @return array{days_overdue: int, aging_bucket: string}
     */
    private function resolveAging(string $status, CarbonInterface $dueDate): array
    {
        $daysOverdue = $status !== 'paid' && $dueDate->isPast()
            ? (int) $dueDate->diffInDays(now())
            : 0;

        return [
            'days_overdue' => $daysOverdue,
            'aging_bucket' => match (true) {
                $status === 'paid' => 'Lunas',
                $daysOverdue === 0 => 'Current',
                $daysOverdue <= 30 => '1–30 hari',
                $daysOverdue <= 60 => '31–60 hari',
                default => '> 60 hari',
            },
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapShipmentsByPaymentMethod(PaymentMethod $method): array
    {
        return array_values(
            Shipment::query()
                ->with(['customer:id,name', 'originBranch:id,code,city'])
                ->where('payment_method', $method->value)
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
                ])
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapTopCustomers(): array
    {
        return array_values(
            Customer::query()
                ->where('is_top_enabled', true)
                ->orderBy('name')
                ->get()
                ->map(function (Customer $c): array {
                    $unbilledQuery = Shipment::query()
                        ->where('customer_id', $c->id)
                        ->where('payment_method', PaymentMethod::TOP->value)
                        ->where('status', ShipmentStatus::DELIVERED->value)
                        ->whereDoesntHave('invoiceLine');

                    return [
                        'id' => $c->id,
                        'code' => $c->code,
                        'name' => $c->name,
                        'top_days' => $c->top_days,
                        'credit_limit' => (float) $c->credit_limit,
                        'credit_exposure' => $this->shipments->customerCreditExposure($c->id),
                        'unbilled_delivered_count' => (clone $unbilledQuery)->count(),
                        'unbilled_delivered_amount' => (float) $unbilledQuery->sum('total_amount'),
                    ];
                })
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapActiveCustomers(): array
    {
        return array_values(
            Customer::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'type', 'is_top_enabled', 'top_days'])
                ->map(static fn (Customer $c): array => [
                    'id' => $c->id,
                    'code' => $c->code,
                    'name' => $c->name,
                    'type' => $c->type,
                    'is_top_enabled' => $c->is_top_enabled,
                    'top_days' => $c->top_days,
                ])
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapActiveVendors(): array
    {
        return array_values(
            Vendor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'type', 'payment_terms_days'])
                ->map(static fn (Vendor $v): array => [
                    'id' => $v->id,
                    'code' => $v->code,
                    'name' => $v->name,
                    'type' => $v->type->value,
                    'type_label' => $v->type->label(),
                    'payment_terms_days' => $v->payment_terms_days,
                ])
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapBranches(): array
    {
        return array_values(
            Branch::query()
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'city'])
                ->map(static fn (Branch $b): array => [
                    'id' => $b->id,
                    'code' => $b->code,
                    'name' => $b->name,
                    'city' => $b->city,
                ])
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapVendorManifests(): array
    {
        return array_values(
            Manifest::query()
                ->with(['originBranch:id,code', 'destinationBranch:id,code'])
                ->whereNotNull('vendor_id')
                ->orderByDesc('id')
                ->limit(30)
                ->get()
                ->map(static fn (Manifest $m): array => [
                    'id' => $m->id,
                    'manifest_number' => $m->manifest_number,
                    'vendor_id' => $m->vendor_id,
                    'vendor_cost' => (float) $m->vendor_cost,
                    'route' => ($m->originBranch->code ?? '?').' → '.($m->destinationBranch->code ?? '?'),
                ])
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapUnbilledVendorShipments(): array
    {
        $billedShipmentIds = VendorBillLine::query()
            ->whereNotNull('shipment_id')
            ->select('shipment_id');

        return array_values(
            Shipment::query()
                ->with(['originBranch:id,code', 'destinationBranch:id,code'])
                ->whereNotNull('vendor_id')
                ->where('vendor_cost', '>', 0)
                ->where('status', '!=', ShipmentStatus::CANCELLED->value)
                ->whereNotIn('id', $billedShipmentIds)
                ->orderByDesc('id')
                ->limit(40)
                ->get()
                ->map(static fn (Shipment $s): array => [
                    'id' => $s->id,
                    'awb_number' => $s->awb_number,
                    'vendor_id' => $s->vendor_id,
                    'vendor_awb_no' => $s->vendor_awb_no,
                    'vendor_cost' => (float) $s->vendor_cost,
                    'route' => ($s->originBranch->code ?? '?').' → '.($s->destinationBranch->code ?? '?'),
                    'service_type' => $s->service_type,
                ])
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapInvoices(): array
    {
        return array_values(
            Invoice::query()
                ->with(['customer:id,name,code,top_days', 'lines'])
                ->orderByDesc('created_at')
                ->get()
                ->map(function (Invoice $inv): array {
                    $aging = $this->resolveAging($inv->status, $inv->due_date);

                    return [
                        'id' => $inv->id,
                        'invoice_number' => $inv->invoice_number,
                        'invoice_type' => $inv->invoice_type,
                        'invoice_type_label' => $inv->invoice_type === 'manual' ? 'Manual' : 'B2B TOP',
                        'customer_name' => $inv->customer->name ?? '-',
                        'period' => $inv->period_start->format('d M').' – '.$inv->period_end->format('d M Y'),
                        'shipment_count' => $inv->lines->count(),
                        'subtotal' => (float) $inv->subtotal,
                        'ppn_amount' => (float) $inv->ppn_amount,
                        'pph23_amount' => (float) $inv->pph23_amount,
                        'total_amount' => (float) $inv->total_amount,
                        'due_date' => $inv->due_date->format('d M Y'),
                        'days_overdue' => $aging['days_overdue'],
                        'aging_bucket' => $aging['aging_bucket'],
                        'status' => $inv->status,
                        'paid_at' => $inv->paid_at?->format('d M Y, H:i'),
                        'payment_reference' => $inv->payment_reference,
                    ];
                })
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapVendorBills(): array
    {
        return array_values(
            VendorBill::query()
                ->with(['vendor:id,name,code,type', 'branch:id,code', 'lines'])
                ->orderByDesc('created_at')
                ->get()
                ->map(function (VendorBill $bill): array {
                    $aging = $this->resolveAging($bill->status, $bill->due_date);

                    return [
                        'id' => $bill->id,
                        'bill_number' => $bill->bill_number,
                        'vendor_invoice_no' => $bill->vendor_invoice_no,
                        'vendor_name' => $bill->vendor->name ?? '-',
                        'vendor_code' => $bill->vendor->code ?? '-',
                        'branch_code' => $bill->branch->code ?? 'HO',
                        'line_count' => $bill->lines->count(),
                        'bill_date' => $bill->bill_date->format('d M Y'),
                        'due_date' => $bill->due_date->format('d M Y'),
                        'subtotal' => (float) $bill->subtotal,
                        'ppn_amount' => (float) $bill->ppn_amount,
                        'pph23_amount' => (float) $bill->pph23_amount,
                        'total_amount' => (float) $bill->total_amount,
                        'days_overdue' => $aging['days_overdue'],
                        'aging_bucket' => $aging['aging_bucket'],
                        'status' => $bill->status,
                        'paid_at' => $bill->paid_at?->format('d M Y, H:i'),
                        'payment_reference' => $bill->payment_reference,
                    ];
                })
                ->all()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapRecentLedger(): array
    {
        return array_values(
            FinancialLedger::query()
                ->with('branch:id,code')
                ->orderByDesc('id')
                ->limit(40)
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
                ])
                ->all()
        );
    }
}
