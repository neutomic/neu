<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres;

use Amp\Postgres\Executor;
use Amp\Postgres\QueryExecutionError;
use Amp\Postgres\Quoter;
use Amp\Sql\ConnectionException;
use Amp\Sql\QueryError;
use Amp\Sql\SqlException;
use Error;
use Neu\Database\ConnectionInterface;
use Neu\Database\Exception;
use Neu\Database\IdentifierQuoterInterface;
use Neu\Database\PreparedStatementInterface;
use Neu\Database\QueryResultInterface;
use Psl\IO;

use function defined;

abstract class AbstractConnection implements ConnectionInterface, IdentifierQuoterInterface
{
    public function __construct(
        private readonly Executor&Quoter $executor,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function prepare(string $query): PreparedStatementInterface
    {
        try {
            return new PreparedStatement($this->executor->prepare($query), $query);
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
                $result = new QueryResult($this->executor->query($query), $query);
            } else {
                $result = new QueryResult($this->executor->execute($query, $parameters), $query);
            }

            if (defined('DATABASE_DEBUG_QUERIES')) {
                /** @psalm-suppress MissingThrowsDocblock */
                IO\write_error_line("\033[91m%s\033[m", $result->getSqlTemplate());
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
    public function getLastUsedAt(): int
    {
        return $this->executor->getLastUsedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function isAlive(): bool
    {
        return $this->executor->isAlive();
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
    public function quoteIdentifier(string $name): string
    {
        try {
            /** @var non-empty-string */
            return $this->executor->quoteName($name);
        } catch (Error $e) {
            throw new Exception\ConnectionException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
