<?php

declare(strict_types=1);

namespace Neu\Database;

use Closure;

interface DatabaseInterface extends AbstractionLayerInterface
{
    /**
     * Creates a transaction that can be used to execute queries in isolation.
     */
    public function createTransaction(TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): TransactionInterface;

    /**
     * Run the given operation in a transaction.
     *
     * Note: any exception throw from the `$operation` will be thrown back to the caller site.
     *
     * @template T
     *
     * @param Closure(TransactionInterface): T $operation
     *
     * @return T
     */
    public function transactional(Closure $operation, TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): mixed;
}
