<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $warehouse_id
 * @property int $storage_bin_id
 * @property int $product_id
 * @property string $movement_type
 * @property string $reference_code
 * @property int $quantity_change
 * @property int $balance_after
 * @property string|null $batch_number
 * @property Carbon|null $expires_at
 * @property string|null $reason
 * @property int|null $actor_id
 * @property Carbon $created_at
 */
class InventoryLedger extends Model
{
    protected $table = 'inventory_ledger';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'storage_bin_id',
        'product_id',
        'movement_type',
        'reference_code',
        'quantity_change',
        'balance_after',
        'batch_number',
        'expires_at',
        'reason',
        'actor_id',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'balance_after' => 'integer',
            'expires_at' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<StorageBin, $this>
     */
    public function storageBin(): BelongsTo
    {
        return $this->belongsTo(StorageBin::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
