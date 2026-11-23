<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Contracts;

use App\Models\FinancialLedger;

interface FinancialLedgerRecorder
{
    public function recordCredit(
        string $prefix,
        ?int $branchId,
        string $channel,
        float $amount,
        string $referenceType,
        int $referenceId,
        string $description,
        ?int $actorId = null,
    ): FinancialLedger;
}
