<?php

declare(strict_types=1);

namespace App\Modules\FIN\Domain\Repositories;

use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepository
{
    public function findForUpdateOrFail(int $id): Invoice;

    /**
     * @return Collection<int, Shipment>
     */
    public function unbilledDeliveredTopShipments(int $customerId): Collection;

    public function nextSequence(string $prefix): int;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  iterable<int, Shipment>  $shipments
     */
    public function createWithLines(array $attributes, iterable $shipments): Invoice;
}
