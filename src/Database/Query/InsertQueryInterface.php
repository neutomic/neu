<?php

declare(strict_types=1);

namespace Neu\Database\Query;

interface InsertQueryInterface extends QueryInterface
{
    /**
     * Specifies values for an insert query indexed by column names.
     *
     * Replaces any previous values, if any.
     *
     * <code>
     *     $result = $database->createQueryBuilder()
     *         ->insert('users')
     *         ->values(['username' => 'user1'], ['username' => 'user2'], ['username' => 'user3'])
     *         ->execute(['user1' => 'foo', 'user2' => 'bar', 'user3' => 'baz'])
     *  ;
     * </code>
     *
     * @param array<non-empty-string, non-empty-string> $first The first row values for the insert query indexed by column names.
     * @param array<non-empty-string, non-empty-string> ...$rest The rest of the rows' values for the insert query indexed by column names.
     */
    public function values(array $first, array ...$rest): static;
}
