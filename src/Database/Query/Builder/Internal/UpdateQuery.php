<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder\Internal;

use Neu\Database\AbstractionLayerInterface;
use Neu\Database\Exception;
use Neu\Database\Query\Type;
use Neu\Database\Query\UpdateQueryInterface;
use Psl\Str;

/**
 * @internal
 */
final class UpdateQuery extends AbstractWhereQuery implements UpdateQueryInterface
{
    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     * @param list<non-empty-string> $sets
     */
    public function __construct(
        AbstractionLayerInterface $dbal,
        private readonly string $table,
        private readonly null|string $alias = null,
        private array $sets = [],
    ) {
        parent::__construct($dbal);
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): Type
    {
        return Type::Update;
    }

    /**
     * Sets a new value for a column.
     *
     * @param non-empty-string $column The column to set.
     * @param non-empty-string $value The value, expression, placeholder, etc.
     */
    public function set(string $column, string $value): static
    {
        $sets = $this->sets;
        $sets[] = $this->dbal->createExpressionBuilder()->equal($column, $value);

        $clone = clone $this;
        $clone->sets = $sets;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        if ($this->sets === []) {
            throw new Exception\LogicException('UpdateQueryInterface::set() must be called at least once before attempting to execute the query.');
        }

        return 'UPDATE ' . $this->getTableSQL($this->table, $this->alias) . ' SET ' . Str\join($this->sets, ', ') . $this->getWhereSQL();
    }
}
