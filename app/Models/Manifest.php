<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $manifest_number
 * @property int $origin_branch_id
 * @property int $destination_branch_id
 * @property int $vehicle_id
 * @property int $driver_id
 * @property string|null $seal_number
 * @property string $driver_advance_amount
 * @property string $status
 * @property Carbon|null $departed_at
 * @property Carbon|null $arrived_at
 * @property int|null $created_by
 */
class Manifest extends Model
{
    protected $fillable = [
        'manifest_number',
        'origin_branch_id',
        'destination_branch_id',
        'vehicle_id',
        'driver_id',
        'seal_number',
        'driver_advance_amount',
        'status',
        'departed_at',
        'arrived_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    /**
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * @return HasMany<ManifestItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ManifestItem::class);
    }
}
