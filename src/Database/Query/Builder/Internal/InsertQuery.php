<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder\Internal;

use Neu\Database\AbstractionLayerInterface;
use Neu\Database\Exception;
use Neu\Database\Query\InsertQueryInterface;
use Neu\Database\Query\Type;
use Psl\Str;
use Psl\Vec;

final class InsertQuery extends AbstractExecutableQuery implements InsertQueryInterface
{
    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     * @param list<array<non-empty-string, string>> $values
     */
    public function __construct(
        AbstractionLayerInterface $dbal,
        private readonly string $table,
        private readonly null|string $alias = null,
        private array $values = [],
    ) {
        parent::__construct($dbal);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\LogicException If no values have been provided, or an inconsistent value is encountered.
     */
    public function __toString(): string
    {
        $columns = null;
        $sets = [];
        foreach ($this->values as $i => $row) {
            $row_columns = Vec\keys($row);
            if ($columns === null) {
                $columns = $row_columns;
            } elseif ($columns !== $row_columns) {
                throw new Exception\LogicException(Str\format('All values must have consistent column names, value #%d is inconsistent.', $i));
            }

            $sets[] = '(' . Str\join(Vec\values($row), ', ') . ')';
        }

        if ($columns === null) {
            throw new Exception\LogicException('InsertQueryInterface::values() must be called at least once before attempting to execute the insert query.');
        }

        return 'INSERT INTO ' . $this->getTableSQL($this->table, $this->alias) . ' (' . Str\join($columns, ', ') . ') VALUES ' . Str\join($sets, ', ');
    }

    /**
     * {@inheritDoc}
     */
    public function values(array $first, array ...$rest): static
    {
        $clone = clone $this;
        $clone->values = Vec\concat([$first], $rest);

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): Type
    {
        return Type::Insert;
    }
}
