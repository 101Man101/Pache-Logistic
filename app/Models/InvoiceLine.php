<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $shipment_id
 * @property string|null $description
 * @property string $quantity
 * @property string $unit
 * @property string $unit_price
 * @property string $amount
 */
class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id',
        'shipment_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'amount',
    ];

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
