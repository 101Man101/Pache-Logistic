<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum SurchargeCalcMode: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
    case PER_KG = 'per_kg';

    public function label(): string
    {
        return match ($this) {
            self::FIXED => 'Nominal Tetap (IDR)',
            self::PERCENTAGE => 'Persentase Ongkir (%)',
            self::PER_KG => 'Per Kilogram (IDR/kg)',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
