<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shipment_id
 * @property int|null $surcharge_id
 * @property string $charge_type
 * @property string|null $code
 * @property string $description
 * @property string $calc_mode
 * @property string $rate_value
 * @property string $quantity
 * @property string $amount
 * @property string|null $notes
 * @property int|null $created_by
 * @property Carbon|null $created_at
 */
class ShipmentCharge extends Model
{
    protected $fillable = [
        'shipment_id',
        'surcharge_id',
        'charge_type',
        'code',
        'description',
        'calc_mode',
        'rate_value',
        'quantity',
        'amount',
        'notes',
        'created_by',
    ];

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * @return BelongsTo<Surcharge, $this>
     */
    public function surcharge(): BelongsTo
    {
        return $this->belongsTo(Surcharge::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
