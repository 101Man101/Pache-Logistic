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
 * @property string $invoice_type
 * @property int $customer_id
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property bool $apply_ppn
 * @property bool $apply_pph23
 * @property string $subtotal
 * @property string $ppn_amount
 * @property string $pph23_amount
 * @property string $total_amount
 * @property Carbon $due_date
 * @property string|null $notes
 * @property string $status
 * @property Carbon|null $paid_at
 * @property string|null $payment_reference
 * @property int|null $created_by
 */
class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'invoice_type',
        'customer_id',
        'period_start',
        'period_end',
        'apply_ppn',
        'apply_pph23',
        'subtotal',
        'ppn_amount',
        'pph23_amount',
        'total_amount',
        'due_date',
        'notes',
        'status',
        'paid_at',
        'payment_reference',
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
            'apply_ppn' => 'boolean',
            'apply_pph23' => 'boolean',
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
