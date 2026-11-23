<?php

declare(strict_types=1);

namespace App\Modules\WMS\Domain\Repositories;

use App\Models\InventoryLedger;
use App\Models\StorageBin;

interface InventoryLedgerRepository
{
    public function lockBinOrFail(int $binId): StorageBin;

    public function latestBalanceForUpdate(int $warehouseId, int $binId, int $productId): int;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function recordMutation(array $attributes): InventoryLedger;

    /**
     * @return list<array<string, mixed>>
     */
    public function latestStockPositions(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function recentEntries(int $limit = 25): array;
}
