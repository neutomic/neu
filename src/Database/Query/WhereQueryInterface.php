<?php

declare(strict_types=1);

namespace Neu\Database\Query;

interface WhereQueryInterface extends QueryInterface
{
    /**
     * Specifies a restriction to the query result.
     *
     * Any previously specified restrictions, if any, will be replaced.
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $expression
     */
    public function where(string|Expression\CompositeExpressionInterface $expression): static;

    /**
     * Adds a restriction to the query results, forming a logical disjunction with any previously specified restrictions.
     *
     * @see where()
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $expression
     */
    public function orWhere(string|Expression\CompositeExpressionInterface $expression): static;

    /**
     * Adds a restriction to the query results, forming a logical conjunction with any previously specified restrictions.
     *
     * @see where()
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $expression
     */
    public function andWhere(string|Expression\CompositeExpressionInterface $expression): static;
}
