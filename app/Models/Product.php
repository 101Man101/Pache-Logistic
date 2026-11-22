<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $sku
 * @property string $name
 * @property string $allocation_rule
 * @property string $unit
 */
class Product extends Model
{
    protected $fillable = [
        'customer_id',
        'sku',
        'name',
        'allocation_rule',
        'unit',
    ];

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<InventoryLedger, $this>
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }
}
