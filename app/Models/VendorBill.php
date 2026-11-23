<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $bill_number
 * @property string|null $vendor_invoice_no
 * @property int $vendor_id
 * @property int|null $branch_id
 * @property int|null $manifest_id
 * @property Carbon $bill_date
 * @property Carbon $due_date
 * @property string $subtotal
 * @property bool $apply_ppn
 * @property bool $apply_pph23
 * @property string $ppn_amount
 * @property string $pph23_amount
 * @property string $total_amount
 * @property string $status
 * @property Carbon|null $paid_at
 * @property string|null $payment_reference
 * @property string|null $notes
 * @property int|null $created_by
 */
class VendorBill extends Model
{
    protected $fillable = [
        'bill_number',
        'vendor_invoice_no',
        'vendor_id',
        'branch_id',
        'manifest_id',
        'bill_date',
        'due_date',
        'subtotal',
        'apply_ppn',
        'apply_pph23',
        'ppn_amount',
        'pph23_amount',
        'total_amount',
        'status',
        'paid_at',
        'payment_reference',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bill_date' => 'date',
            'due_date' => 'date',
            'apply_ppn' => 'boolean',
            'apply_pph23' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Vendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<Manifest, $this>
     */
    public function manifest(): BelongsTo
    {
        return $this->belongsTo(Manifest::class);
    }

    /**
     * @return HasMany<VendorBillLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(VendorBillLine::class);
    }
}
