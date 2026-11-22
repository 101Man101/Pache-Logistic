<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shipment_id
 * @property int|null $branch_id
 * @property string $status
 * @property string $location
 * @property string $description
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int|null $actor_id
 * @property Carbon $created_at
 */
class TrackingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shipment_id',
        'branch_id',
        'status',
        'location',
        'description',
        'latitude',
        'longitude',
        'actor_id',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
