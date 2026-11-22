<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string $city
 * @property string $address
 * @property string|null $phone
 * @property string|null $latitude
 * @property string|null $longitude
 * @property bool $is_active
 */
class Branch extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'city',
        'address',
        'phone',
        'latitude',
        'longitude',
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
     * @return HasMany<Warehouse, $this>
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}
