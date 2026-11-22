<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $entry_code
 * @property int|null $branch_id
 * @property string $channel
 * @property string $direction
 * @property string $amount
 * @property string $reference_type
 * @property int $reference_id
 * @property string $description
 * @property int|null $actor_id
 * @property Carbon $created_at
 */
class FinancialLedger extends Model
{
    protected $table = 'financial_ledger';

    public $timestamps = false;

    protected $fillable = [
        'entry_code',
        'branch_id',
        'channel',
        'direction',
        'amount',
        'reference_type',
        'reference_id',
        'description',
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
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
