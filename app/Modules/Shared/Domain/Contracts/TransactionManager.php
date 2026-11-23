<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Contracts;

interface TransactionManager
{
    /**
     * Execute the given callback within an atomic database transaction.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function run(callable $callback): mixed;
}
