<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shipment_id
 * @property string $description
 * @property int $qty
 * @property string $actual_weight_kg
 * @property string $length_cm
 * @property string $width_cm
 * @property string $height_cm
 */
class ShipmentItem extends Model
{
    protected $fillable = [
        'shipment_id',
        'description',
        'qty',
        'actual_weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
