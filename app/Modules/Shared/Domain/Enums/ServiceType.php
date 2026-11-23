<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum ServiceType: string
{
    case REG = 'REG';
    case EXP = 'EXP';
    case CRG = 'CRG';

    public function defaultDivisor(): int
    {
        return match ($this) {
            self::EXP => 5000,
            self::CRG => 4000,
            self::REG => 6000,
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
