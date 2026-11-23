<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Shared\Domain\Enums\SurchargeCalcMode;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property SurchargeCalcMode $calc_mode
 * @property string $amount
 * @property string|null $description
 * @property bool $is_active
 */
class Surcharge extends Model
{
    protected $fillable = [
        'code',
        'name',
        'calc_mode',
        'amount',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'calc_mode' => SurchargeCalcMode::class,
            'is_active' => 'boolean',
        ];
    }

    public function computeAmount(float $shippingCost, float $chargeableWeightKg): float
    {
        $rate = (float) $this->amount;

        return match ($this->calc_mode) {
            SurchargeCalcMode::PERCENTAGE => round($shippingCost * ($rate / 100), 2),
            SurchargeCalcMode::PER_KG => round($chargeableWeightKg * $rate, 2),
            SurchargeCalcMode::FIXED => round($rate, 2),
        };
    }
}
