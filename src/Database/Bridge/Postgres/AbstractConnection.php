<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres;

use Amp\Postgres\PostgresExecutor;
use Amp\Postgres\QueryExecutionError;
use Amp\Sql\ConnectionException;
use Amp\Sql\QueryError;
use Amp\Sql\SqlException;
use Error;
use Neu\Database\ConnectionInterface;
use Neu\Database\Exception;
use Neu\Database\IdentifierQuoterInterface;
use Neu\Database\LiteralQuoterInterface;
use Neu\Database\PreparedStatementInterface;
use Neu\Database\QueryResultInterface;
use Psl\IO;

use function defined;

abstract class AbstractConnection implements ConnectionInterface, IdentifierQuoterInterface, LiteralQuoterInterface
{
    public function __construct(
        private readonly PostgresExecutor $executor,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function prepare(string $query): PreparedStatementInterface
    {
        try {
            $statement = $this->executor->prepare($query);

            return new PreparedStatement($statement, $query);
        } catch (QueryError $e) {
            throw new Exception\InvalidQueryException($e->getMessage(), $e->getCode(), $e);
        } catch (ConnectionException $e) {
            throw new Exception\ConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function query(string $query, array $parameters = []): QueryResultInterface
    {
        try {
            if ($parameters === []) {
                // Allow multiple commands in a single query when not using prepared statement.
                $result = new QueryResult($this->executor->query($query));
            } else {
                $result = new QueryResult($this->executor->execute($query, $parameters));
            }

            if (defined('DATABASE_DEBUG_QUERIES')) {
                /** @psalm-suppress MissingThrowsDocblock */
                IO\write_error_line("\033[91m%s\033[m", $query);
            }

            return $result;
        } catch (QueryError | QueryExecutionError $e) {
            throw new Exception\InvalidQueryException($e->getMessage(), $e->getCode(), $e);
        } catch (ConnectionException $e) {
            throw new Exception\ConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getNotifier(string $channel): Notification\Notifier
    {
        return new Notification\Notifier($this->executor, $channel);
    }

    /**
     * {@inheritDoc}
     */
    public function getLastUsedAt(): int
    {
        return $this->executor->getLastUsedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function isClosed(): bool
    {
        return $this->executor->isClosed();
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->executor->close();
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifier(string $identifier): string
    {
        try {
            /** @var non-empty-string */
            return $this->executor->quoteName($identifier);
        } catch (Error $e) {
            throw new Exception\ConnectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function quoteLiteral(string $literal): string
    {
        try {
            /** @var non-empty-string */
            return $this->executor->quoteString($literal);
        } catch (Error $e) {
            throw new Exception\ConnectionException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
