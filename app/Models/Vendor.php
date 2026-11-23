<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Shared\Domain\Enums\VendorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property VendorType $type
 * @property string|null $contact_person
 * @property string $phone
 * @property string|null $email
 * @property string|null $address
 * @property int $payment_terms_days
 * @property string $base_cost_per_kg
 * @property string|null $notes
 * @property bool $is_active
 */
class Vendor extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'contact_person',
        'phone',
        'email',
        'address',
        'payment_terms_days',
        'base_cost_per_kg',
        'notes',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => VendorType::class,
            'payment_terms_days' => 'integer',
            'is_active' => 'boolean',
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
     * @return HasMany<Manifest, $this>
     */
    public function manifests(): HasMany
    {
        return $this->hasMany(Manifest::class);
    }

    /**
     * @return HasMany<VendorBill, $this>
     */
    public function bills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }
}
