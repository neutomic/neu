<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder\Internal;

use Neu\Database\AbstractionLayerInterface;
use Neu\Database\Query\DeleteQueryInterface;
use Neu\Database\Query\Type;

/**
 * @internal
 */
final class DeleteQuery extends AbstractWhereQuery implements DeleteQueryInterface
{
    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     */
    public function __construct(
        AbstractionLayerInterface $dbal,
        protected readonly string $table,
        protected readonly null|string $alias = null,
    ) {
        parent::__construct($dbal);
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): Type
    {
        return Type::Delete;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return 'DELETE FROM ' . $this->getTableSQL($this->table, $this->alias) . $this->getWhereSQL();
    }
}
