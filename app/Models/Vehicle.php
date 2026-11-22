<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $plate_number
 * @property string $type
 * @property string $capacity_kg
 * @property string $capacity_cbm
 * @property Carbon $stnk_expires_at
 * @property Carbon $kir_expires_at
 * @property string $status
 */
class Vehicle extends Model
{
    protected $fillable = [
        'branch_id',
        'plate_number',
        'type',
        'capacity_kg',
        'capacity_cbm',
        'stnk_expires_at',
        'kir_expires_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stnk_expires_at' => 'date',
            'kir_expires_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
