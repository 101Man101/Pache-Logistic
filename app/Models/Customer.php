<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string|null $npwp
 * @property string $phone
 * @property string|null $email
 * @property string $address
 * @property bool $is_top_enabled
 * @property int $top_days
 * @property string $credit_limit
 * @property bool $is_active
 */
class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'npwp',
        'phone',
        'email',
        'address',
        'is_top_enabled',
        'top_days',
        'credit_limit',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_top_enabled' => 'boolean',
            'is_active' => 'boolean',
            'top_days' => 'integer',
        ];
    }

    /**
     * @return HasMany<Shipment, $this>
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Calculate current TOP credit exposure (BR-02):
     * Unpaid Invoices + Unbilled active TOP Shipments.
     */
    public function currentCreditExposure(): float
    {
        $unpaidInvoices = (float) $this->invoices()
            ->whereIn('status', ['unpaid', 'overdue'])
            ->sum('total_amount');

        $unbilledShipments = (float) $this->shipments()
            ->where('payment_method', PaymentMethod::TOP->value)
            ->where('status', '!=', ShipmentStatus::CANCELLED->value)
            ->whereDoesntHave('invoiceLine')
            ->sum('total_amount');

        return $unpaidInvoices + $unbilledShipments;
    }
}
