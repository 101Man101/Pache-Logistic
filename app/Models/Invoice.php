<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $invoice_number
 * @property int $customer_id
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property string $subtotal
 * @property string $ppn_amount
 * @property string $pph23_amount
 * @property string $total_amount
 * @property Carbon $due_date
 * @property string $status
 * @property Carbon|null $paid_at
 * @property int|null $created_by
 */
class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'period_start',
        'period_end',
        'subtotal',
        'ppn_amount',
        'pph23_amount',
        'total_amount',
        'due_date',
        'status',
        'paid_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<InvoiceLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }
}
