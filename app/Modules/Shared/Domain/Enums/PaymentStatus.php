<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case BILLED = 'billed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::PAID => 'Lunas',
            self::BILLED => 'Kredit TOP (B2B)',
        };
    }

    public function isClearedForDispatch(): bool
    {
        return $this === self::PAID || $this === self::BILLED;
    }
}
