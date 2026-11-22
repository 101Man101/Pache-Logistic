<?php

declare(strict_types=1);

namespace App\Modules\WMS\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InventoryLedger;
use App\Models\Product;
use App\Models\StorageBin;
use App\Models\Warehouse;
use App\Modules\WMS\Application\UseCases\RecordStockMutationUseCase;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class InventoryController extends Controller
{
    public function index(): Response
    {
        $warehouses = Warehouse::query()->with('branch:id,code,city')->get();
        $bins = StorageBin::query()->with('warehouse:id,code,name')->orderBy('code')->get();
        $products = Product::query()->with('customer:id,name')->orderBy('sku')->get();

        // Latest balance per (warehouse, bin, product) from immutable ledger
        $allEntries = InventoryLedger::query()
            ->with(['warehouse:id,code,name', 'storageBin:id,code,zone', 'product:id,sku,name,allocation_rule,unit'])
            ->orderByDesc('id')
            ->get();

        $stockPositions = $allEntries
            ->unique(static fn (InventoryLedger $row): string => "{$row->warehouse_id}-{$row->storage_bin_id}-{$row->product_id}")
            ->values()
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
            ]);

        $recentLedger = $allEntries->take(25)->map(static fn (InventoryLedger $row): array => [
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
        ])->values();

        return Inertia::render('wms/Index', [
            'warehouses' => $warehouses,
            'bins' => $bins,
            'products' => $products,
            'stockPositions' => $stockPositions,
            'recentLedger' => $recentLedger,
        ]);
    }

    public function mutate(Request $request, RecordStockMutationUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'storage_bin_id' => ['required', 'integer', 'exists:storage_bins,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'movement_type' => ['required', Rule::in(['inbound', 'outbound', 'adjustment'])],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reference_code' => ['required', 'string', 'max:64'],
            'batch_number' => ['nullable', 'string', 'max:48'],
            'expires_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $entry = $useCase->execute([
                'warehouse_id' => (int) $validated['warehouse_id'],
                'storage_bin_id' => (int) $validated['storage_bin_id'],
                'product_id' => (int) $validated['product_id'],
                'movement_type' => $validated['movement_type'],
                'quantity' => (int) $validated['quantity'],
                'reference_code' => $validated['reference_code'],
                'batch_number' => $validated['batch_number'] ?? null,
                'expires_at' => $validated['expires_at'] ?? null,
                'reason' => $validated['reason'] ?? null,
            ], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Mutasi stok tercatat di Immutable Ledger (saldo baru: {$entry->balance_after}).");
    }
}
