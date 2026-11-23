<?php

declare(strict_types=1);

namespace App\Modules\FIN\Infrastructure\Persistence;

use App\Models\Customer;
use App\Models\FinancialLedger;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;

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
                'pending_gate_count' => Shipment::query()->where('payment_status', PaymentStatus::PENDING->value)->count(),
            ],
            'cashShipments' => $this->mapShipmentsByPaymentMethod(PaymentMethod::CASH),
            'onlineShipments' => $this->mapShipmentsByPaymentMethod(PaymentMethod::ONLINE),
            'topCustomers' => $this->mapTopCustomers(),
            'invoices' => $this->mapInvoices(),
            'ledger' => $this->mapRecentLedger(),
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
    private function mapInvoices(): array
    {
        return array_values(
            Invoice::query()
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
                ])
                ->all()
        );
    }
}
