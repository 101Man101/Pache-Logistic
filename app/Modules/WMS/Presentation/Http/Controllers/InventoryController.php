<?php

declare(strict_types=1);

namespace App\Modules\WMS\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StorageBin;
use App\Models\Warehouse;
use App\Modules\WMS\Application\UseCases\RecordStockMutationUseCase;
use App\Modules\WMS\Domain\Repositories\InventoryLedgerRepository;
use App\Modules\WMS\Presentation\Http\Requests\RecordStockMutationRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class InventoryController extends Controller
{
    public function index(InventoryLedgerRepository $ledger): Response
    {
        return Inertia::render('wms/Index', [
            'warehouses' => Warehouse::query()->with('branch:id,code,city')->get(),
            'bins' => StorageBin::query()->with('warehouse:id,code,name')->orderBy('code')->get(),
            'products' => Product::query()->with('customer:id,name')->orderBy('sku')->get(),
            'stockPositions' => $ledger->latestStockPositions(),
            'recentLedger' => $ledger->recentEntries(25),
        ]);
    }

    public function mutate(RecordStockMutationRequest $request, RecordStockMutationUseCase $useCase): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $entry = $useCase->execute([
                'warehouse_id' => (int) $validated['warehouse_id'],
                'storage_bin_id' => (int) $validated['storage_bin_id'],
                'product_id' => (int) $validated['product_id'],
                'movement_type' => (string) $validated['movement_type'],
                'quantity' => (int) $validated['quantity'],
                'reference_code' => (string) $validated['reference_code'],
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
