<?php

declare(strict_types=1);

namespace Neu\Database;

interface TransactionInterface extends AbstractionLayerInterface
{
    /**
     * Get the transaction isolation level.
     */
    public function getIsolationLevel(): TransactionIsolationLevel;

    /**
     * @return bool True if the transaction is active, false if it has been committed or rolled back.
     */
    public function isActive(): bool;

    /**
     * Commits the transaction and makes it inactive.
     *
     * @throws Exception\TransactionException If the transaction has been committed or rolled back.
     */
    public function commit(): void;

    /**
     * Rolls back the transaction and makes it inactive.
     *
     * @throws Exception\TransactionException If the transaction has been committed or rolled back.
     */
    public function rollback(): void;

    /**
     * Creates a savepoint with the given identifier.
     *
     * @param non-empty-string $identifier Savepoint identifier.
     *
     * @throws Exception\TransactionException If the transaction has been committed or rolled back.
     */
    public function createSavepoint(string $identifier): void;

    /**
     * Rolls back to the savepoint with the given identifier.
     *
     * @param non-empty-string $identifier Savepoint identifier.
     *
     * @throws Exception\TransactionException If the transaction has been committed or rolled back.
     */
    public function rollbackTo(string $identifier): void;

    /**
     * Releases the savepoint with the given identifier.
     *
     * @param non-empty-string $identifier Savepoint identifier.
     *
     * @throws Exception\TransactionException If the transaction has been committed or rolled back.
     */
    public function releaseSavepoint(string $identifier): void;
}
