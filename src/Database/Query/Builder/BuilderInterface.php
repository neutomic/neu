<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder;

use Neu\Database\Query;

interface BuilderInterface
{
    /**
     * Create a select query.
     *
     * Example:
     *
     *      $query = $database
     *          ->createQueryBuilder()
     *          ->select('u.id')
     *          ->from('users', 'u')
     *          ->distinct()
     *      ;
     *
     * @param non-empty-string $select
     * @param non-empty-string ...$selects
     */
    public function select(string $select, string ...$selects): Query\SelectQueryInterface;

    /**
     * Create a delete query.
     *
     * Example:
     *
     *      $expr = $database->createExpressionBuilder();
     *      $database
     *          ->createQueryBuilder()
     *          ->delete('user', 'u')
     *          ->where($expr->or(
     *              $expr->equal('u.username', ':username'),
     *              $expr->like('u.email', ':suspicious_email'),
     *              $expr->like('u.email', ':another_suspicious_email')
     *          ))
     *          ->execute(['username' => 'foo', 'suspicious_email' => '%example.com', 'another_suspicious_email' => '%example.net'])
     *      ;
     *
     * @param non-empty-string $table
     * @param non-empty-string|null $alias
     */
    public function delete(string $table, ?string $alias = null): Query\DeleteQueryInterface;

    /**
     * Create an update query.
     *
     * Example:
     *
     *      $expr = $database->createExpressionBuilder();
     *      $database
     *          ->createQueryBuilder()
     *          ->update('user', 'u')
     *          ->set('u.password', ':password')
     *          ->where($expr->equal('u.username', ':identifier'))
     *          ->orWhere($expr->equal('u.email', ':identifier'))
     *          ->execute(['identifier' => $identifier, 'password' => $password])
     *      ;
     *
     * @param non-empty-string $table
     * @param non-empty-string|null $alias
     */
    public function update(string $table, ?string $alias = null): Query\UpdateQueryInterface;

    /**
     * Create an insert query.
     *
     * Example:
     *
     *      $database
     *          ->createQueryBuilder()
     *          ->insert('user')
     *          ->values(
     *              ['username' => ':username1', 'email' => ':email1', 'password' => ':password1'],
     *              ['username' => ':username2', 'email' => ':email2', 'password' => ':password2'],
     *          )
     *          ->execute([
     *              'username1' => 'foo',
     *              'username2' => 'bar',
     *              'email1' => 'foo@example.com',
     *              'email2' => 'bar@example.com',
     *              'password1' => 'fooPass',
     *              'password2' => 'barPass',
     *          ])
     *      ;
     *
     * @param non-empty-string $table
     * @param non-empty-string|null $alias
     */
    public function insert(string $table): Query\InsertQueryInterface;
}
