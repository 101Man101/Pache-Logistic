<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Modules\Shared\Domain\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

final readonly class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction(static fn () => $callback());
    }
}
