<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder\Internal;

use Neu\Database\AbstractionLayerInterface;
use Neu\Database\Exception;
use Neu\Database\OrderDirection;
use Neu\Database\Query\Expression;
use Neu\Database\Query\SelectQueryInterface;
use Neu\Database\Query\Type;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

final class SelectQuery extends AbstractWhereQuery implements SelectQueryInterface
{
    private bool $distinct = false;
    /**
     * @var list<array{non-empty-string, ?non-empty-string}>
     */
    private array $from = [];

    /**
     * @var array<non-empty-string, list<array{JoinType, non-empty-string, non-empty-string, ?non-empty-string}>>
     */
    private array $joins = [];

    /**
     * @var list<non-empty-string>
     */
    private array $groupBy = [];

    /**
     * @var non-empty-string|Expression\CompositeExpressionInterface|null
     */
    private string|Expression\CompositeExpressionInterface|null $having = null;

    /**
     * @var array<non-empty-string, OrderDirection>
     */
    private array $orderBy = [];

    /**
     * @var int<0, max>
     */
    private int $offset = 0;

    /**
     * @var null|int<0, max>
     */
    private ?int $limit = null;

    /**
     * @param non-empty-list<string> $select
     */
    public function __construct(
        AbstractionLayerInterface $dbal,
        private readonly array $select,
    ) {
        parent::__construct($dbal);
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): Type
    {
        return Type::Select;
    }

    /**
     * {@inheritDoc}
     */
    public function distinct(): static
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function from(string $table, ?string $alias = null): static
    {
        $this->from[] = [$table, $alias];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function innerJoin(string $from, string $join, string $alias, ?string $condition = null): static
    {
        $this->joins[$from][] = [JoinType::Inner, $join, $alias, $condition];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function leftJoin(string $from, string $join, string $alias, ?string $condition = null): static
    {
        $this->joins[$from][] = [JoinType::Left, $join, $alias, $condition];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function rightJoin(string $from, string $join, string $alias, ?string $condition = null): static
    {
        $this->joins[$from][] = [JoinType::Right, $join, $alias, $condition];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function groupBy(string $expression, string ...$expressions): static
    {
        $this->groupBy = Vec\concat([$expression], $expressions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function andGroupBy(string $expression, string ...$expressions): static
    {
        $this->groupBy = Vec\concat($this->groupBy, [$expression], $expressions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function andHaving(Expression\CompositeExpressionInterface|string $restriction): SelectQueryInterface
    {
        $previous_restriction = $this->having;
        if (null !== $previous_restriction) {
            if ($previous_restriction instanceof Expression\CompositeExpressionInterface && $previous_restriction->getType() === Expression\CompositionType::Conjunction) {
                $restriction = $previous_restriction->with((string) $restriction);
            } else {
                $restriction = Expression\CompositeExpression::and($previous_restriction, $restriction);
            }
        }

        return $this->having($restriction);
    }

    /**
     * {@inheritDoc}
     */
    public function having(Expression\CompositeExpressionInterface|string $restriction): SelectQueryInterface
    {
        $this->having = $restriction;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orHaving(Expression\CompositeExpressionInterface|string $restriction): SelectQueryInterface
    {
        $previous_restriction = $this->having;
        if (null !== $previous_restriction) {
            if ($previous_restriction instanceof Expression\CompositeExpressionInterface && $previous_restriction->getType() === Expression\CompositionType::Disjunction) {
                $restriction = $previous_restriction->with((string) $restriction);
            } else {
                $restriction = Expression\CompositeExpression::or($previous_restriction, $restriction);
            }
        }

        return $this->having($restriction);
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(string $sort, OrderDirection $direction = OrderDirection::Ascending): static
    {
        $this->orderBy = [$sort => $direction];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function andOrderBy(string $sort, OrderDirection $direction = OrderDirection::Ascending): static
    {
        $this->orderBy[$sort] = $direction;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return 'SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . Str\join($this->select, ', ')
            . $this->getFromSQL()
            . $this->getWhereSQL()
            . $this->getGroupBySQL()
            . $this->getHavingSQL()
            . $this->getOrderBySQL()
            . $this->getLimitSQL();
    }

    /**
     * @throws Exception\LogicException If the query state is not valid.
     */
    private function getFromSQL(): string
    {
        return $this->from ? (' FROM ' . $this->getFromClausesSQL()) : '';
    }

    /**
     * @throws Exception\LogicException If the query state is not valid.
     */
    private function getFromClausesSQL(): string
    {
        $from_clauses = [];
        $known_aliases = [];

        // Loop through all FROM clauses
        foreach ($this->from as [$table, $alias]) {
            $reference = $alias ?? $table;

            $known_aliases[] = $reference;
            [$joins, $known_aliases] = $this->getJoinSQL($reference, $known_aliases);
            $from_clauses[$reference] = $table . ($alias === null ? '' : ' ' . $alias) . $joins;
        }

        $this->verifyAllAliasesAreKnown($known_aliases);

        return Str\join(Vec\values($from_clauses), ', ');
    }

    /**
     * @param non-empty-string $table
     * @param list<non-empty-string> $known_aliases
     *
     * @throws Exception\LogicException If the query state is not valid.
     *
     * @return array{string, list<non-empty-string>}
     */
    private function getJoinSQL(string $table, array $known_aliases): array
    {
        $sql = '';
        if (Iter\contains_key($this->joins, $table)) {
            foreach ($this->joins[$table] as [$kind, $join, $alias, $condition]) {
                if (Iter\contains($known_aliases, $alias)) {
                    throw new Exception\LogicException(Str\format(
                        'The given alias `%s` is not unique in FROM or JOIN clause table. The currently registered aliases are: `%s`.',
                        $alias,
                        Str\join($known_aliases, '`, `'),
                    ));
                }

                $sql .= ' ' . $kind->value . ' JOIN ' . $join . ' ' . $alias;
                if ($condition !== null) {
                    $sql .= ' ON ' . $condition;
                }

                $known_aliases[] = $alias;
            }

            foreach ($this->joins[$table] as [$_kind, $_join, $alias, $_condition]) {
                [$join_sql, $known_aliases] = $this->getJoinSQL($alias, $known_aliases);

                $sql .= $join_sql;
            }
        }

        return [$sql, $known_aliases];
    }

    /**
     * @param list<non-empty-string> $known_aliases
     *
     * @throws Exception\LogicException If the query state is not valid.
     */
    private function verifyAllAliasesAreKnown(array $known_aliases): void
    {
        foreach ($this->joins as $alias => $_) {
            if (!Iter\contains($known_aliases, $alias)) {
                throw new Exception\LogicException(Str\format(
                    'The given alias `%s` is not part of any FROM or JOIN clause table. The currently registered aliases are: `%s`.',
                    $alias,
                    Str\join($known_aliases, '`, `'),
                ));
            }
        }
    }

    private function getGroupBySQL(): string
    {
        if ([] === $this->groupBy) {
            return '';
        }

        return ' GROUP BY ' . Str\join($this->groupBy, ', ');
    }

    private function getHavingSQL(): string
    {
        if ($this->having === null) {
            return '';
        }

        return ' HAVING ' . ((string)$this->having);
    }

    private function getOrderBySQL(): string
    {
        if ($this->orderBy === []) {
            return '';
        }

        return ' ORDER BY ' . Str\join(
            Vec\map_with_key(
                $this->orderBy,
                static fn(string $sort, OrderDirection $direction): string => $sort . ' ' . $direction->value,
            ),
            ', ',
        );
    }

    private function getLimitSQL(): string
    {
        $sql = '';
        if ($this->limit !== null) {
            $sql .= Str\format(' LIMIT %d', $this->limit);

            if ($this->offset > 0) {
                $sql .= Str\format(' OFFSET %d', $this->offset);
            }
        }

        return $sql;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchOneNumeric(array $parameters = []): ?array
    {
        $row = $this->fetchOneAssociative();
        if (null === $row) {
            return null;
        }

        return Vec\values($row);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchOneAssociative(array $parameters = []): ?array
    {
        $result = $this->execute($parameters);
        $rows = $result->getRows();
        unset($result);

        return $rows[0] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAllNumeric(array $parameters = []): array
    {
        return Vec\map($this->fetchAllAssociative($parameters), static fn($row) => Vec\values($row));
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAllAssociative(array $parameters = []): array
    {
        return $this->execute($parameters)->getRows();
    }
}
