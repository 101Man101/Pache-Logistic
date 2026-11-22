<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class AwbNumber
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^PCH-[A-Z0-9]{3}-\d{6}-\d{5}$/', $value)) {
            throw new InvalidArgumentException("Format nomor AWB Pache Logistic tidak valid: {$value}");
        }
    }

    public static function generate(string $branchCode, string $dateYmd, int $sequence): self
    {
        $code = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $branchCode) ?: 'JKT', 0, 3));
        $padded = str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);

        return new self("PCH-{$code}-{$dateYmd}-{$padded}");
    }
}
