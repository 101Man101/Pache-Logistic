<?php

declare(strict_types=1);

namespace App\Modules\FIN\Domain\Repositories;

use App\Models\VendorBill;

interface VendorBillRepository
{
    public function findForUpdateOrFail(int $id): VendorBill;

    public function nextSequence(string $prefix): int;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>  $lines
     */
    public function createWithLines(array $attributes, array $lines): VendorBill;
}
