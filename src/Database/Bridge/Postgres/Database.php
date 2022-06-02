<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres;

use Amp\Postgres\Connection;
use Amp\Postgres\QueryExecutionError;
use Amp\Sql\ConnectionException;
use Amp\Sql\QueryError;
use Amp\Sql\SqlException;
use Amp\Sql\TransactionIsolationLevel as AmpTransactionIsolationLevel;
use Closure;
use Neu\Database\DatabaseInterface;
use Neu\Database\Exception;
use Neu\Database\TransactionInterface;
use Neu\Database\TransactionIsolationLevel;
use Throwable;

final class Database extends AbstractConnection implements DatabaseInterface
{
    use AbstractionLayerTrait;

    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct($this->connection);
    }

    /**
     * {@inheritDoc}
     */
    public function getListener(string $channel): Notification\Listener
    {
        try {
            return new Notification\Listener($this->connection->listen($channel), $channel);
        } catch (ConnectionException $e) {
            throw new Exception\ConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlException | QueryError | QueryExecutionError $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function transactional(Closure $operation, TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): mixed
    {
        $transaction = $this->createTransaction($isolation);
        try {
            $result = $operation($transaction);
            /** @psalm-suppress MissingThrowsDocblock */
            $transaction->commit();

            return $result;
        } catch (Throwable $exception) {
            /** @psalm-suppress MissingThrowsDocblock */
            $transaction->rollback();

            /** @psalm-suppress MissingThrowsDocblock */
            throw $exception;
        }
    }

    public function createTransaction(TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): TransactionInterface
    {
        $level = match ($isolation) {
            TransactionIsolationLevel::ReadCommitted => AmpTransactionIsolationLevel::Committed,
            TransactionIsolationLevel::ReadUncommitted => AmpTransactionIsolationLevel::Uncommitted,
            TransactionIsolationLevel::RepeatableRead => AmpTransactionIsolationLevel::Repeatable,
            TransactionIsolationLevel::Serializable => AmpTransactionIsolationLevel::Serializable,
        };

        $transaction = $this->connection->beginTransaction($level);

        return new Transaction($transaction, $isolation);
    }
}
