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
            $invoice->lines()->create([
                'shipment_id' => $shipment->id,
                'amount' => $shipment->total_amount,
            ]);
        }

        return $invoice->load(['customer', 'lines.shipment']);
    }
}
