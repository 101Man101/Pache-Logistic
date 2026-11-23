<?php

declare(strict_types=1);

namespace App\Modules\WMS\Application\UseCases;

use App\Models\InventoryLedger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\WMS\Domain\Exceptions\InsufficientBinStockException;
use App\Modules\WMS\Domain\Repositories\InventoryLedgerRepository;

/**
 * Immutable Stock Ledger Mutation with Pessimistic Lock (BR-04 & FR-WMS-03).
 */
final readonly class RecordStockMutationUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private InventoryLedgerRepository $ledger,
    ) {}

    /**
     * @param  array{
     *     warehouse_id: int,
     *     storage_bin_id: int,
     *     product_id: int,
     *     movement_type: string,
     *     quantity: int,
     *     reference_code: string,
     *     batch_number?: string|null,
     *     expires_at?: string|null,
     *     reason?: string|null
     * }  $payload
     */
    public function execute(array $payload, ?int $actorId = null): InventoryLedger
    {
        return $this->transactions->run(function () use ($payload, $actorId): InventoryLedger {
            $bin = $this->ledger->lockBinOrFail($payload['storage_bin_id']);
            $currentBalance = $this->ledger->latestBalanceForUpdate($payload['warehouse_id'], $bin->id, $payload['product_id']);

            $qty = abs($payload['quantity']);
            $change = match ($payload['movement_type']) {
                'outbound' => -$qty,
                'adjustment' => $payload['quantity'],
                default => $qty,
            };

            $balanceAfter = $currentBalance + $change;
            if ($balanceAfter < 0) {
                throw InsufficientBinStockException::forBin($bin->code, $currentBalance, abs($change));
            }

            return $this->ledger->recordMutation([
                'warehouse_id' => $payload['warehouse_id'],
                'storage_bin_id' => $bin->id,
                'product_id' => $payload['product_id'],
                'movement_type' => $payload['movement_type'],
                'reference_code' => $payload['reference_code'],
                'quantity_change' => $change,
                'balance_after' => $balanceAfter,
                'batch_number' => $payload['batch_number'] ?? null,
                'expires_at' => $payload['expires_at'] ?? null,
                'reason' => $payload['reason'] ?? null,
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);
        });
    }
}
