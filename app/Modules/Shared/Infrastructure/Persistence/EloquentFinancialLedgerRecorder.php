<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Models\FinancialLedger;
use App\Modules\Shared\Domain\Contracts\FinancialLedgerRecorder;

final readonly class EloquentFinancialLedgerRecorder implements FinancialLedgerRecorder
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
    ): FinancialLedger {
        return $this->recordEntry('credit', $prefix, $branchId, $channel, $amount, $referenceType, $referenceId, $description, $actorId);
    }

    public function recordDebit(
        string $prefix,
        ?int $branchId,
        string $channel,
        float $amount,
        string $referenceType,
        int $referenceId,
        string $description,
        ?int $actorId = null,
    ): FinancialLedger {
        return $this->recordEntry('debit', $prefix, $branchId, $channel, $amount, $referenceType, $referenceId, $description, $actorId);
    }

    private function recordEntry(
        string $direction,
        string $prefix,
        ?int $branchId,
        string $channel,
        float $amount,
        string $referenceType,
        int $referenceId,
        string $description,
        ?int $actorId = null,
    ): FinancialLedger {
        $entryCode = $prefix.'-'.now()->format('ymdHis').'-'.str_pad((string) $referenceId, 4, '0', STR_PAD_LEFT);

        /** @var FinancialLedger */
        return FinancialLedger::query()->create([
            'entry_code' => $entryCode,
            'branch_id' => $branchId,
            'channel' => $channel,
            'direction' => $direction,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'actor_id' => $actorId,
            'created_at' => now(),
        ]);
    }
}
