<?php

declare(strict_types=1);

namespace Neu\Database;

interface LiteralQuoterInterface
{
    /**
     * Quotes (escapes) the given string for use as a literal in a query.
     *
     * @param non-empty-string $name Unquoted literal.
     *
     * @throws Exception\ConnectionException If the connection to the database has been closed.
     *
     * @return non-empty-string Quoted literal.
     */
    public function quoteLiteral(string $literal): string;
}
