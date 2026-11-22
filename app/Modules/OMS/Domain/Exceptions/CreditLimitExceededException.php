<?php

declare(strict_types=1);

namespace App\Modules\OMS\Domain\Exceptions;

use DomainException;

final class CreditLimitExceededException extends DomainException
{
    public static function forCustomer(string $customerName, float $exposure, float $creditLimit): self
    {
        $expFmt = number_format($exposure, 0, ',', '.');
        $limFmt = number_format($creditLimit, 0, ',', '.');

        return new self(
            "Penerbitan resi TOP ditolak (BR-02): eksposur kredit {$customerName} (Rp {$expFmt}) melampaui Credit Limit (Rp {$limFmt})."
        );
    }
}
