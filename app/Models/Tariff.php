<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $origin_branch_id
 * @property int $destination_branch_id
 * @property string $service_type
 * @property int $volumetric_divisor
 * @property string $min_weight_kg
 * @property string $rate_per_kg
 * @property string $insurance_rate_pct
 * @property int $sla_days
 * @property bool $is_active
 */
class Tariff extends Model
{
    protected $fillable = [
        'origin_branch_id',
        'destination_branch_id',
        'service_type',
        'volumetric_divisor',
        'min_weight_kg',
        'rate_per_kg',
        'insurance_rate_pct',
        'sla_days',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'volumetric_divisor' => 'integer',
            'sla_days' => 'integer',
            'is_active' => 'boolean',
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
}
