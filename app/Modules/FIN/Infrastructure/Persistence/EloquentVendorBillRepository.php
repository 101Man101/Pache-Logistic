<?php

declare(strict_types=1);

namespace App\Modules\FIN\Infrastructure\Persistence;

use App\Models\VendorBill;
use App\Modules\FIN\Domain\Repositories\VendorBillRepository;

final readonly class EloquentVendorBillRepository implements VendorBillRepository
{
    public function findForUpdateOrFail(int $id): VendorBill
    {
        /** @var VendorBill */
        return VendorBill::query()->with(['vendor', 'branch', 'manifest'])->lockForUpdate()->findOrFail($id);
    }

    public function nextSequence(string $prefix): int
    {
        $lastBill = VendorBill::query()
            ->where('bill_number', 'like', "{$prefix}%")
            ->orderByDesc('bill_number')
            ->lockForUpdate()
            ->value('bill_number');

        return is_string($lastBill) ? ((int) substr($lastBill, -4) + 1) : 1;
    }

    public function createWithLines(array $attributes, array $lines): VendorBill
    {
        /** @var VendorBill $bill */
        $bill = VendorBill::query()->create($attributes);

        foreach ($lines as $line) {
            $bill->lines()->create($line);
        }

        return $bill->load(['vendor', 'branch', 'manifest', 'lines.shipment']);
    }
}
