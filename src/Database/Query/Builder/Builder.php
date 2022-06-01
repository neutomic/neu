<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder;

use Neu\Database\AbstractionLayerInterface;
use Neu\Database\Query;

final class Builder implements BuilderInterface
{
    public function __construct(
        private readonly AbstractionLayerInterface $dbal
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function select(string $select, string ...$selects): Query\SelectQueryInterface
    {
        return new Internal\SelectQuery($this->dbal, [$select, ...$selects]);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $table, ?string $alias = null): Query\DeleteQueryInterface
    {
        return new Internal\DeleteQuery($this->dbal, $table, $alias);
    }

    /**
     * {@inheritDoc}
     */
    public function update(string $table, ?string $alias = null): Query\UpdateQueryInterface
    {
        return new Internal\UpdateQuery($this->dbal, $table, $alias);
    }

    /**
     * {@inheritDoc}
     */
    public function insert(string $table): Query\InsertQueryInterface
    {
        return new Internal\InsertQuery($this->dbal, $table);
    }
}
