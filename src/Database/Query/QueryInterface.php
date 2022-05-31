<?php

declare(strict_types=1);

namespace Neu\Database\Query;

use Neu\Database\Exception;
use Neu\Database\PreparedStatementInterface;
use Neu\Database\QueryResultInterface;
use Stringable;

interface QueryInterface extends Stringable
{
    /**
     * Retrieve the query type.
     */
    public function getType(): Type;

    /**
     * Execute this query using the optionally provided `$parameters`.
     *
     * @param array<non-empty-string, mixed> $parameters
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\LogicException If the query state is not valid.
     */
    public function execute(array $parameters = []): QueryResultInterface;

    /**
     * Prepares this query.
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\LogicException If the query state is not valid.
     */
    public function prepare(): PreparedStatementInterface;

    /**
     * @throws Exception\ConnectionException If the connection to the database has been closed.
     * @throws Exception\LogicException If the query state is not valid.
     *
     * @return non-empty-string
     */
    public function __toString(): string;
}
