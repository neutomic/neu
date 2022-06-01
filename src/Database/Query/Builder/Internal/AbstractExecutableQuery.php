<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder\Internal;

use Neu\Database\AbstractionLayerInterface;
use Neu\Database\Exception;
use Neu\Database\PreparedStatementInterface;
use Neu\Database\Query\QueryInterface;
use Neu\Database\QueryResultInterface;

/**
 * A Query that can be executed.
 *
 * @internal
 */
abstract class AbstractExecutableQuery implements QueryInterface
{
    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     */
    public function __construct(
        protected readonly AbstractionLayerInterface $dbal,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $parameters = []): QueryResultInterface
    {
        return $this->dbal->query((string) $this, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function prepare(): PreparedStatementInterface
    {
        return $this->dbal->prepare((string) $this);
    }

    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     *
     * @throws Exception\ConnectionException If the connection to the database has been closed.
     *
     * @return non-empty-string
     */
    protected function getTableSQL(string $table, ?string $alias = null): string
    {
        return $this->dbal->quoteIdentifier($table) . ($alias !== null ? (' ' . $this->dbal->quoteIdentifier($alias)) : '');
    }
}
