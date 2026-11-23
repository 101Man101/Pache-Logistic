<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum VendorType: string
{
    case LINEHAUL = 'linehaul';
    case AIR_FREIGHT = 'air_freight';
    case SEA_FREIGHT = 'sea_freight';
    case LAST_MILE = 'last_mile';
    case CUSTOMS = 'customs';

    public function label(): string
    {
        return match ($this) {
            self::LINEHAUL => 'Linehaul Truk',
            self::AIR_FREIGHT => 'Kargo Udara (SMU)',
            self::SEA_FREIGHT => 'Kargo Laut',
            self::LAST_MILE => 'Kurir Last-Mile',
            self::CUSTOMS => 'Kepabeanan / Forwarding',
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
