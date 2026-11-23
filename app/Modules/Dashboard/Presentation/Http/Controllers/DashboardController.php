<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FinancialLedger;
use App\Models\Invoice;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\VendorBill;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $period = (string) $request->string('period', 'month');

        $recentShipments = Shipment::query()
            ->with(['customer:id,name', 'originBranch:id,code,city', 'destinationBranch:id,code,city'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(static fn (Shipment $s): array => [
                'id' => $s->id,
                'awb_number' => $s->awb_number,
                'customer_name' => $s->customer->name ?? '-',
                'route' => ($s->originBranch->code ?? '?').' → '.($s->destinationBranch->code ?? '?'),
                'service_type' => $s->service_type,
                'chargeable_weight_kg' => (float) $s->chargeable_weight_kg,
                'total_amount' => (float) $s->total_amount,
                'payment_method' => $s->payment_method->value,
                'payment_method_label' => $s->payment_method->shortLabel(),
                'payment_status' => $s->payment_status->value,
                'payment_status_label' => $s->payment_status->label(),
                'status' => $s->status->value,
                'status_label' => $s->status->label(),
                'created_at' => $s->created_at?->format('d M Y, H:i'),
            ]);

        return Inertia::render('Dashboard', [
            'period' => $period,
            'recentShipments' => $recentShipments,
            'summaryMetrics' => Inertia::defer(fn (): array => $this->buildMetrics()),
            'channelBreakdown' => Inertia::defer(fn (): array => $this->buildChannelBreakdown()),
            'alerts' => Inertia::defer(fn (): array => $this->buildAlerts()),
        ]);
    }

    public function globalSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        $shipments = Shipment::query()
            ->where('awb_number', 'like', "%{$q}%")
            ->orWhere('reference_no', 'like', "%{$q}%")
            ->orWhere('consignee_name', 'like', "%{$q}%")
            ->orWhere('consignee_phone', 'like', "%{$q}%")
            ->limit(5)
            ->get(['awb_number', 'consignee_name', 'status', 'payment_method'])
            ->map(static fn (Shipment $s): array => [
                'type' => 'AWB',
                'title' => $s->awb_number,
                'subtitle' => "{$s->consignee_name} · {$s->payment_method->shortLabel()} · {$s->status->label()}",
                'href' => "/oms/shipments/{$s->awb_number}",
            ]);

        $manifests = Manifest::query()
            ->where('manifest_number', 'like', "%{$q}%")
            ->limit(3)
            ->get(['manifest_number', 'status'])
            ->map(static fn (Manifest $m): array => [
                'type' => 'MANIFEST',
                'title' => $m->manifest_number,
                'subtitle' => 'Status: '.strtoupper($m->status->value),
                'href' => '/tms/manifests',
            ]);

        $customers = Customer::query()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('code', 'like', "%{$q}%")
            ->limit(3)
            ->get(['code', 'name', 'type', 'is_top_enabled'])
            ->map(static fn (Customer $c): array => [
                'type' => 'CUSTOMER',
                'title' => $c->name,
                'subtitle' => $c->code.($c->is_top_enabled ? ' · Kontrak TOP' : ' · Retail Non-COD'),
                'href' => '/master',
            ]);

        $invoices = Invoice::query()
            ->with('customer:id,name')
            ->where('invoice_number', 'like', "%{$q}%")
            ->limit(3)
            ->get(['id', 'invoice_number', 'customer_id', 'status'])
            ->map(static fn (Invoice $inv): array => [
                'type' => 'INVOICE',
                'title' => $inv->invoice_number,
                'subtitle' => ($inv->customer->name ?? '-').' · '.strtoupper($inv->status),
                'href' => "/finance/invoices/{$inv->id}",
            ]);

        $vendorBills = VendorBill::query()
            ->with('vendor:id,name')
            ->where('bill_number', 'like', "%{$q}%")
            ->orWhere('vendor_invoice_no', 'like', "%{$q}%")
            ->limit(3)
            ->get(['id', 'bill_number', 'vendor_invoice_no', 'vendor_id', 'status'])
            ->map(static fn (VendorBill $bill): array => [
                'type' => 'VENDOR BILL',
                'title' => $bill->bill_number,
                'subtitle' => ($bill->vendor->name ?? '-').($bill->vendor_invoice_no ? " · {$bill->vendor_invoice_no}" : ''),
                'href' => "/finance/vendor-bills/{$bill->id}",
            ]);

        return response()->json([
            'items' => $shipments
                ->concat($manifests)
                ->concat($customers)
                ->concat($invoices)
                ->concat($vendorBills)
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMetrics(): array
    {
        $totalShipments = Shipment::query()->count();
        $deliveredCount = Shipment::query()->where('status', ShipmentStatus::DELIVERED->value)->count();
        $inTransitCount = Shipment::query()->whereIn('status', [
            ShipmentStatus::ORIGIN_HUB->value,
            ShipmentStatus::IN_TRANSIT->value,
            ShipmentStatus::DESTINATION_HUB->value,
            ShipmentStatus::OUT_FOR_DELIVERY->value,
        ])->count();

        $totalRevenue = (float) Shipment::query()
            ->where('status', '!=', ShipmentStatus::CANCELLED->value)
            ->sum('total_amount');

        $paidLedgerTotal = (float) FinancialLedger::query()->where('direction', 'credit')->sum('amount');
        $pendingPaymentCount = Shipment::query()->where('payment_status', PaymentStatus::PENDING->value)->count();
        $unpaidInvoicesTotal = (float) Invoice::query()->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount');

        $epodCompliance = $deliveredCount > 0
            ? round((Shipment::query()->where('status', ShipmentStatus::DELIVERED->value)->whereNotNull('epod_receiver_name')->count() / $deliveredCount) * 100, 1)
            : 100.0;

        return [
            'total_shipments' => $totalShipments,
            'in_transit_count' => $inTransitCount,
            'delivered_count' => $deliveredCount,
            'total_revenue' => $totalRevenue,
            'paid_ledger_total' => $paidLedgerTotal,
            'pending_payment_count' => $pendingPaymentCount,
            'unpaid_invoices_total' => $unpaidInvoicesTotal,
            'epod_compliance_pct' => $epodCompliance,
        ];
    }

    /**
     * @return list<array{key: string, label: string, count: int, amount: float, share_pct: float}>
     */
    private function buildChannelBreakdown(): array
    {
        $total = max(1.0, (float) Shipment::query()->sum('total_amount'));

        return array_map(static function (PaymentMethod $method) use ($total): array {
            $query = Shipment::query()->where('payment_method', $method->value);
            $amount = (float) $query->sum('total_amount');

            return [
                'key' => $method->value,
                'label' => $method->label(),
                'count' => $query->count(),
                'amount' => $amount,
                'share_pct' => round(($amount / $total) * 100, 1),
            ];
        }, PaymentMethod::cases());
    }

    /**
     * @return list<array{severity: string, code: string, title: string, detail: string, href: string}>
     */
    private function buildAlerts(): array
    {
        $alerts = [];

        $pendingCount = Shipment::query()->where('payment_status', PaymentStatus::PENDING->value)->count();
        if ($pendingCount > 0) {
            $alerts[] = [
                'severity' => 'warning',
                'code' => 'BR-08',
                'title' => "{$pendingCount} Resi Menunggu Pelunasan (Non-COD Gate)",
                'detail' => 'Resi Cash/Online berstatus PENDING otomatis ditahan dari Scan Bagging & Manifest.',
                'href' => '/finance',
            ];
        }

        $expiringVehicles = Vehicle::query()
            ->where('stnk_expires_at', '<=', now()->addDays(30))
            ->orWhere('kir_expires_at', '<=', now()->addDays(30))
            ->get();

        foreach ($expiringVehicles as $v) {
            $expired = $v->stnk_expires_at->isPast() || $v->kir_expires_at->isPast();
            $alerts[] = [
                'severity' => $expired ? 'danger' : 'warning',
                'code' => 'BR-06',
                'title' => "Armada {$v->plate_number} ({$v->type})",
                'detail' => $expired
                    ? 'STNK/Uji KIR sudah kedaluwarsa — diblokir dari penugasan Manifest baru.'
                    : 'Dokumen STNK/KIR jatuh tempo dalam < 30 hari. Segera jadwalkan perpanjangan.',
                'href' => '/tms/manifests',
            ];
        }

        $unbilledTopCount = Shipment::query()
            ->where('payment_method', PaymentMethod::TOP->value)
            ->where('status', ShipmentStatus::DELIVERED->value)
            ->whereDoesntHave('invoiceLine')
            ->count();

        if ($unbilledTopCount > 0) {
            $alerts[] = [
                'severity' => 'info',
                'code' => 'FR-FIN-03',
                'title' => "{$unbilledTopCount} Resi TOP Delivered Siap Auto-Invoicing",
                'detail' => 'Buka modul Finance → B2B TOP untuk menerbitkan Invoice + lampiran e-POD.',
                'href' => '/finance',
            ];
        }

        return $alerts;
    }
}
