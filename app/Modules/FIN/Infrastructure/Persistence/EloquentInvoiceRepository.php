<?php

declare(strict_types=1);

namespace App\Modules\FIN\Infrastructure\Persistence;

use App\Models\Invoice;
use App\Models\Shipment;
use App\Modules\FIN\Domain\Repositories\InvoiceRepository;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Collection;

final readonly class EloquentInvoiceRepository implements InvoiceRepository
{
    public function findForUpdateOrFail(int $id): Invoice
    {
        /** @var Invoice */
        return Invoice::query()->with('customer')->lockForUpdate()->findOrFail($id);
    }

    public function unbilledDeliveredTopShipments(int $customerId): Collection
    {
        return Shipment::query()
            ->with(['originBranch:id,code', 'destinationBranch:id,code'])
            ->where('customer_id', $customerId)
            ->where('payment_method', PaymentMethod::TOP->value)
            ->where('status', ShipmentStatus::DELIVERED->value)
            ->whereDoesntHave('invoiceLine')
            ->lockForUpdate()
            ->get();
    }

    public function nextSequence(string $prefix): int
    {
        $lastInvoice = Invoice::query()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('invoice_number')
            ->lockForUpdate()
            ->value('invoice_number');

        return is_string($lastInvoice) ? ((int) substr($lastInvoice, -4) + 1) : 1;
    }

    public function createWithLines(array $attributes, iterable $shipments): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::query()->create($attributes);

        foreach ($shipments as $shipment) {
            $origin = $shipment->originBranch->code ?? '?';
            $dest = $shipment->destinationBranch->code ?? '?';
            $amount = (float) $shipment->total_amount;

            $invoice->lines()->create([
                'shipment_id' => $shipment->id,
                'description' => "Pengiriman {$shipment->awb_number} ({$origin} → {$dest} · {$shipment->service_type} · {$shipment->chargeable_weight_kg} kg)",
                'quantity' => 1,
                'unit' => 'resi',
                'unit_price' => $amount,
                'amount' => $amount,
            ]);
        }

        return $invoice->load(['customer', 'lines.shipment']);
    }

    public function createManualWithLines(array $attributes, array $lines): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::query()->create($attributes);

        foreach ($lines as $line) {
            $invoice->lines()->create($line);
        }

        return $invoice->load(['customer', 'lines']);
    }
}
