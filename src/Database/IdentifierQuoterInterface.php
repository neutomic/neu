<?php

declare(strict_types=1);

namespace Neu\Database;

interface IdentifierQuoterInterface
{
    /**
     * Quotes (escapes) the given string for use as a name or identifier in a query.
     *
     * @param non-empty-string $name Unquoted identifier.
     *
     * @throws Exception\ConnectionException If the connection to the database has been closed.
     *
     * @return non-empty-string Quoted identifier.
     */
    public function quoteIdentifier(string $name): string;
}
