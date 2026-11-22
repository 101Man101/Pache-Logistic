<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $awb_number
 * @property string|null $reference_no
 * @property int $customer_id
 * @property int $origin_branch_id
 * @property int $destination_branch_id
 * @property string $shipper_name
 * @property string $shipper_phone
 * @property string $shipper_address
 * @property string $consignee_name
 * @property string $consignee_phone
 * @property string $consignee_address
 * @property string $service_type
 * @property int $volumetric_divisor
 * @property string $actual_weight_kg
 * @property string $volumetric_weight_kg
 * @property string $chargeable_weight_kg
 * @property string $shipping_cost
 * @property string $insurance_cost
 * @property string $total_amount
 * @property PaymentMethod $payment_method
 * @property PaymentStatus $payment_status
 * @property string|null $payment_reference
 * @property Carbon|null $paid_at
 * @property ShipmentStatus $status
 * @property Carbon|null $delivered_at
 * @property string|null $epod_receiver_name
 * @property string|null $epod_receiver_relation
 * @property string|null $epod_latitude
 * @property string|null $epod_longitude
 * @property string|null $epod_notes
 * @property string|null $epod_photo_url
 * @property int|null $created_by
 */
class Shipment extends Model
{
    protected $fillable = [
        'awb_number',
        'reference_no',
        'customer_id',
        'origin_branch_id',
        'destination_branch_id',
        'shipper_name',
        'shipper_phone',
        'shipper_address',
        'consignee_name',
        'consignee_phone',
        'consignee_address',
        'service_type',
        'volumetric_divisor',
        'actual_weight_kg',
        'volumetric_weight_kg',
        'chargeable_weight_kg',
        'shipping_cost',
        'insurance_cost',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'paid_at',
        'status',
        'delivered_at',
        'epod_receiver_name',
        'epod_receiver_relation',
        'epod_latitude',
        'epod_longitude',
        'epod_notes',
        'epod_photo_url',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'status' => ShipmentStatus::class,
            'volumetric_divisor' => 'integer',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
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

    /**
     * @return HasMany<ShipmentItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * @return HasMany<TrackingLog, $this>
     */
    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * @return HasOne<InvoiceLine, $this>
     */
    public function invoiceLine(): HasOne
    {
        return $this->hasOne(InvoiceLine::class);
    }

    /**
     * @return HasMany<ManifestItem, $this>
     */
    public function manifestItems(): HasMany
    {
        return $this->hasMany(ManifestItem::class);
    }
}
