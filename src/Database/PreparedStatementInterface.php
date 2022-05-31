<?php

declare(strict_types=1);

namespace Neu\Database;

interface PreparedStatementInterface
{
    /**
     * Execute the prepared statement.
     *
     * @param array<string, mixed> $parameters
     */
    public function execute(array $parameters = []): QueryResultInterface;

    /**
     * Retrieve the SQL query template used to prepare this statement.
     */
    public function getSqlTemplate(): string;

    /**
     * Get the timestamp of the last usage of this connection.
     *
     * @return int Unix timestamps in seconds.
     */
    public function getLastUsedAt(): int;

    /**
     * Check if the connection is still alive.
     */
    public function isAlive(): bool;
}
