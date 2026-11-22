<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $code
 * @property string $name
 * @property string $zone_type
 * @property bool $is_active
 */
class Warehouse extends Model
{
    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'zone_type',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return HasMany<StorageBin, $this>
     */
    public function bins(): HasMany
    {
        return $this->hasMany(StorageBin::class);
    }
}
