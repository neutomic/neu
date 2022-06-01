<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder\Internal;

use Neu\Database\Query\Expression;
use Neu\Database\Query\WhereQueryInterface;

/**
 * @internal
 */
abstract class AbstractWhereQuery extends AbstractExecutableQuery implements WhereQueryInterface
{
    /**
     * @var null|non-empty-string|Expression\CompositeExpressionInterface
     */
    protected null|string|Expression\CompositeExpressionInterface $where = null;

    /**
     * Specifies a restriction to the query result.
     *
     * Any previously specified restrictions, if any, will be replaced.
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $expression
     */
    public function where(string|Expression\CompositeExpressionInterface $expression): static
    {
        $clone = clone $this;
        $clone->where = $expression;

        return $clone;
    }

    /**
     * Adds a restriction to the query results, forming a logical disjunction with any previously specified restrictions.
     *
     * @see where()
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $expression
     */
    public function orWhere(string|Expression\CompositeExpressionInterface $expression): static
    {
        if ($this->where === null) {
            return $this->where($expression);
        }

        $where = $this->where;
        if ($where instanceof Expression\CompositeExpressionInterface && $where->getType() === Expression\CompositionType::Disjunction) {
            $where = $where->with((string) $expression);
        } else {
            $where = Expression\CompositeExpression::or($where, $expression);
        }

        return $this->where($where);
    }

    /**
     * Adds a restriction to the query results, forming a logical conjunction with any previously specified restrictions.
     *
     * @see where()
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $expression
     */
    public function andWhere(string|Expression\CompositeExpressionInterface $expression): static
    {
        if ($this->where === null) {
            return $this->where($expression);
        }

        $where = $this->where;
        if ($where instanceof Expression\CompositeExpressionInterface && $where->getType() === Expression\CompositionType::Conjunction) {
            $where = $where->with((string) $expression);
        } else {
            $where = Expression\CompositeExpression::and($where, $expression);
        }

        return $this->where($where);
    }

    protected function getWhereSQL(): string
    {
        if ($this->where === null) {
            return '';
        }

        $where = $this->where;
        if ($where instanceof Expression\CompositeExpressionInterface) {
            $where = (string) $where;
        }

        return ' WHERE ' . $where;
    }
}
