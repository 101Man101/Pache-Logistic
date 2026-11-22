<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $manifest_id
 * @property int $shipment_id
 * @property Carbon $scanned_at
 * @property Carbon|null $unloaded_at
 */
class ManifestItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'manifest_id',
        'shipment_id',
        'scanned_at',
        'unloaded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'unloaded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Manifest, $this>
     */
    public function manifest(): BelongsTo
    {
        return $this->belongsTo(Manifest::class);
    }

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
