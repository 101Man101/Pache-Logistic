<?php

declare(strict_types=1);

namespace App\Modules\WMS\Infrastructure\Persistence;

use App\Models\InventoryLedger;
use App\Models\StorageBin;
use App\Modules\WMS\Domain\Repositories\InventoryLedgerRepository;
use Illuminate\Support\Facades\DB;

final readonly class EloquentInventoryLedgerRepository implements InventoryLedgerRepository
{
    public function lockBinOrFail(int $binId): StorageBin
    {
        /** @var StorageBin */
        return StorageBin::query()->lockForUpdate()->findOrFail($binId);
    }

    public function latestBalanceForUpdate(int $warehouseId, int $binId, int $productId): int
    {
        /** @var InventoryLedger|null $latest */
        $latest = InventoryLedger::query()
            ->where('warehouse_id', $warehouseId)
            ->where('storage_bin_id', $binId)
            ->where('product_id', $productId)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        return $latest !== null ? $latest->balance_after : 0;
    }

    public function recordMutation(array $attributes): InventoryLedger
    {
        /** @var InventoryLedger */
        return InventoryLedger::query()->create($attributes);
    }

    public function latestStockPositions(): array
    {
        $latestIds = InventoryLedger::query()
            ->select(DB::raw('MAX(id) as latest_id'))
            ->groupBy('warehouse_id', 'storage_bin_id', 'product_id');

        return array_values(
            InventoryLedger::query()
                ->with(['warehouse:id,code,name', 'storageBin:id,code,zone', 'product:id,sku,name,allocation_rule,unit'])
                ->whereIn('id', $latestIds)
                ->orderByDesc('id')
                ->get()
                ->map(static fn (InventoryLedger $row): array => [
                    'warehouse_code' => $row->warehouse->code ?? '-',
                    'bin_code' => $row->storageBin->code ?? '-',
                    'zone' => $row->storageBin->zone ?? '-',
                    'sku' => $row->product->sku ?? '-',
                    'product_name' => $row->product->name ?? '-',
                    'allocation_rule' => $row->product->allocation_rule ?? 'FIFO',
                    'unit' => $row->product->unit ?? 'pcs',
                    'balance' => $row->balance_after,
                    'batch_number' => $row->batch_number,
                    'expires_at' => $row->expires_at?->format('d M Y'),
                ])
                ->all()
        );
    }

    public function recentEntries(int $limit = 25): array
    {
        return array_values(
            InventoryLedger::query()
                ->with(['storageBin:id,code', 'product:id,sku,name'])
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(static fn (InventoryLedger $row): array => [
                    'id' => $row->id,
                    'movement_type' => $row->movement_type,
                    'reference_code' => $row->reference_code,
                    'bin_code' => $row->storageBin->code ?? '-',
                    'sku' => $row->product->sku ?? '-',
                    'product_name' => $row->product->name ?? '-',
                    'quantity_change' => $row->quantity_change,
                    'balance_after' => $row->balance_after,
                    'batch_number' => $row->batch_number,
                    'reason' => $row->reason,
                    'created_at' => $row->created_at->format('d M Y, H:i'),
                ])
                ->all()
        );
    }
}
