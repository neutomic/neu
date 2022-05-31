<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres;

use Amp\Postgres\Transaction as PostgresTransaction;
use Amp\Sql\TransactionError;
use Neu\Database\Exception\TransactionException;
use Neu\Database\TransactionInterface;
use Neu\Database\TransactionIsolationLevel;

final class Transaction extends AbstractConnection implements TransactionInterface
{
    use AbstractionLayerTrait;

    public function __construct(
        private readonly PostgresTransaction $transaction,
        private readonly TransactionIsolationLevel $isolationLevel,
    ) {
        parent::__construct($this->transaction);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsolationLevel(): TransactionIsolationLevel
    {
        return $this->isolationLevel;
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(): bool
    {
        return $this->transaction->isActive();
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): void
    {
        try {
            $this->transaction->commit();
        } catch (TransactionError $e) {
            throw new TransactionException($e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rollback(): void
    {
        try {
            $this->transaction->rollback();
        } catch (TransactionError $e) {
            throw new TransactionException($e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createSavepoint(string $identifier): void
    {
        try {
            $this->transaction->createSavepoint($identifier);
        } catch (TransactionError $e) {
            throw new TransactionException($e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rollbackTo(string $identifier): void
    {
        try {
            $this->transaction->rollbackTo($identifier);
        } catch (TransactionError $e) {
            throw new TransactionException($e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function releaseSavepoint(string $identifier): void
    {
        try {
            $this->transaction->releaseSavepoint($identifier);
        } catch (TransactionError $e) {
            throw new TransactionException($e->getMessage(), previous: $e);
        }
    }
}
