<?php

declare(strict_types=1);

namespace App\Modules\WMS\Domain\Exceptions;

use DomainException;

final class InsufficientBinStockException extends DomainException
{
    public static function forBin(string $binCode, int $available, int $requested): self
    {
        return new self(
            "Stok Bin {$binCode} tidak mencukupi (BR-04): tersedia {$available} unit, diminta {$requested} unit. Mutasi negatif ditolak."
        );
    }
}
