<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum ManifestStatus: string
{
    case OPEN = 'open';
    case DEPARTED = 'departed';
    case ARRIVED = 'arrived';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open (Scan Bagging)',
            self::DEPARTED => 'Departed (In Transit)',
            self::ARRIVED => 'Arrived (Hub Tujuan)',
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
