<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vendor_bill_id
 * @property int|null $shipment_id
 * @property string $description
 * @property string $quantity
 * @property string $unit
 * @property string $unit_price
 * @property string $amount
 */
class VendorBillLine extends Model
{
    protected $fillable = [
        'vendor_bill_id',
        'shipment_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'amount',
    ];

    /**
     * @return BelongsTo<VendorBill, $this>
     */
    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class);
    }

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
