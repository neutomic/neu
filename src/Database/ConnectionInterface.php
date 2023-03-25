<?php

declare(strict_types=1);

namespace Neu\Database;

interface ConnectionInterface
{
    /**
     * Prepares an SQL statement.
     *
     * @param non-empty-string $query The SQL statement to prepare.
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     */
    public function prepare(string $query): PreparedStatementInterface;

    /**
     * Execute the given `$query` using optionally provided `$parameters`.
     *
     * @param non-empty-string $query
     * @param array<string, mixed> $parameters
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     */
    public function query(string $query, array $parameters = []): QueryResultInterface;

    /**
     * Retrieve a notifier for the given channel.
     *
     * @param non-empty-string $channel The channel identifier
     */
    public function getNotifier(string $channel): Notification\NotifierInterface;

    /**
     * Get the timestamp of the last usage of this connection.
     *
     * @return int Unix timestamps in seconds.
     */
    public function getLastUsedAt(): int;

    /**
     * Check if the connection is closed.
     */
    public function isClosed(): bool;

    /**
     * Close the connection to the server.
     *
     * No further operations might be performed.
     */
    public function close(): void;
}
