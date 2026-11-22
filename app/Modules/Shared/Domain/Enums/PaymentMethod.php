<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

/**
 * Strictly Non-COD payment channels for Pache Logistic.
 * COD (Cash on Delivery) is intentionally prohibited at the domain level.
 */
enum PaymentMethod: string
{
    case ONLINE = 'online';
    case CASH = 'cash';
    case TOP = 'top';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE => 'Online Payment (QRIS / VA)',
            self::CASH => 'Cash (Loket Asal)',
            self::TOP => 'B2B Credit (TOP)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::ONLINE => 'ONLINE',
            self::CASH => 'CASH',
            self::TOP => 'B2B TOP',
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
