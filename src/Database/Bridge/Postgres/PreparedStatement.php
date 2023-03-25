<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres;

use Amp\Postgres\PostgresStatement;
use Neu\Database\PreparedStatementInterface;
use Neu\Database\QueryResultInterface;

final class PreparedStatement implements PreparedStatementInterface
{
    /**
     * @param non-empty-string $sql
     */
    public function __construct(
        private readonly PostgresStatement $statement,
        private readonly string $sql,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $parameters = []): QueryResultInterface
    {
        return new QueryResult($this->statement->execute($parameters));
    }

    /**
     * {@inheritDoc}
     */
    public function getSqlTemplate(): string
    {
        return $this->sql;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastUsedAt(): int
    {
        return $this->statement->getLastUsedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function isClosed(): bool
    {
        return $this->statement->isClosed();
    }
}
