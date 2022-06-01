<?php

declare(strict_types=1);

namespace Neu\Database;

use Neu\Database\Query\SelectQueryInterface;
use Neu\Database\Query\UpdateQueryInterface;
use Psl\Str;
use Psl\Vec;

/**
 * @require-implements AbstractionLayerInterface
 *
 * @psalm-suppress MixedAssignment
 * @psalm-suppress UnnecessaryVarAnnotation
 * @psalm-suppress MissingThrowsDocblock
 */
trait AbstractionLayerConvenienceMethodsTrait
{
    /**
     * Creates a query builder that can be used to execute queries through the abstraction layer.
     */
    public function createQueryBuilder(): Query\Builder\BuilderInterface
    {
        return new Query\Builder\Builder($this);
    }

    /**
     * Creates an expression builder to be used for building queries.
     */
    public function createExpressionBuilder(): Query\Expression\BuilderInterface
    {
        return new Query\Expression\Builder();
    }

    /**
     * Insert one row into the given table.
     *
     * Example:
     *
     * ```php
     * $database->insert('users', ['username' => 'azjezz', 'password' => $hash]);
     * ```
     *
     * @param non-empty-string $table
     * @param array<non-empty-string, mixed> $row
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     */
    public function insert(string $table, array $row): QueryResultInterface
    {
        if ($row === []) {
            return $this->query('INSERT INTO ' . $table . ' () VALUES ()');
        }

        $values = [];
        $parameters = [];
        foreach ($row as $column => $value) {
            [$name, $placeholder] = $this->buildPlaceholder($column, 'insert');

            $values[$column] = $placeholder;
            $parameters[$name] = $value;
        }

        return $this->createQueryBuilder()->insert($table)->values($values)->execute($parameters);
    }

    /**
     * Insert multiple rows into the given table.
     *
     * ```php
     * $database->insert('notes', [
     *  ['content' => 'Hello, World!'],
     *  ['content' => 'from Neu!'],
     * ]);
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<array<non-empty-string, mixed>> $rows
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\LogicException If $rows is empty, or have inconsistent column names.
     */
    public function insertAll(string $table, array $rows): QueryResultInterface
    {
        $parameters = [];
        $set = [];
        foreach ($rows as $index => $row) {
            $values = [];
            foreach ($row as $column => $value) {
                [$name, $placeholder] = $this->buildPlaceholder($column, 'insert', $index);

                $values[$column] = $placeholder;
                $parameters[$name] = $value;
            }

            $set[] = $values;
        }

        return $this->createQueryBuilder()->insert($table)->values(...$set)->execute($parameters);
    }

    /**
     * Executes an SQL UPDATE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * Example:
     *
     * ```php
     * $database->update('users', ['password' => $hash], criteria: ['username' => 'azjezz']);
     * ```
     *
     * @param non-empty-string $table Table name
     * @param non-empty-array<non-empty-string, mixed> $data Column-value pairs
     * @param non-empty-array<non-empty-string, mixed> $criteria Update criteria
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\LogicException If $criteria, or $data are empty.
     */
    public function update(string $table, array $data, array $criteria): QueryResultInterface
    {
        $query = $this->createQueryBuilder()->update($table);
        $values = [];
        foreach ($data as $column => $value) {
            [$name, $placeholder] = $this->buildPlaceholder($column, 'update');
            $query = $query->set($column, $placeholder);
            $values[$name] = $value;
        }

        /**
         * @var UpdateQueryInterface $query
         */
        [$query, $values] = $this->buildCriteria($query, $criteria, $values);

        return $query->execute($values);
    }

    /**
     * Executes an SQL DELETE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * Example:
     *
     * ```php
     * $database->delete('users', criteria: ['username' => 'azjezz']);
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-array<non-empty-string, mixed> $criteria Delete criteria
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error)
     * @throws Exception\LogicException If $criteria is empty.
     */
    public function delete(string $table, array $criteria): QueryResultInterface
    {
        [$query, $values] = $this->buildCriteria(
            $this->createQueryBuilder()->delete($table),
            $criteria,
        );

        return $query->execute($values);
    }

    /**
     * Fetch one row from the given table, where columns are index using their names.
     *
     * Example:
     *
     * ```php
     *  $row = $database->fetchOne('articles', ['title', 'content'], criteria: ['id' => 341]);
     *  if (null === $row) {
     *    // handle not found
     *  }
     *
     *  ['title' => $title, 'content' => $content] = $row;
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<string, mixed> $criteria
     * @param array<non-empty-string, OrderDirection> $order_by
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, or $order_by contains invalid values.
     *
     * @return null|array<string, mixed>
     */
    public function fetchOneAssociative(string $table, array $fields = ['*'], array $criteria = [], array $order_by = []): ?array
    {
        /**
         * @var SelectQueryInterface $query
         */
        [$query, $values] = $this->buildCriteria(
            $this->createQueryBuilder()->select(...$fields)->from($table),
            $criteria
        );

        foreach ($order_by as $sort => $direction) {
            $query = $query->andOrderBy($sort, $direction);
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return $query->fetchOneAssociative($values);
    }

    /**
     * Fetch one row from the given table, where columns are index using numeric values.
     *
     * Example:
     *
     * ```php
     *  $row = $database->fetchOne('articles', ['title', 'content'], criteria: ['id' => 341]);
     *  if (null === $row) {
     *    // handle not found
     *  }
     *
     *  [$title, $content] = $row;
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<string, mixed> $criteria
     * @param array<non-empty-string, OrderDirection> $order_by
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, or $order_by contains invalid values.
     *
     * @return null|list<mixed>
     */
    public function fetchOneNumeric(string $table, array $fields = ['*'], array $criteria = [], array $order_by = []): ?array
    {
        $row = $this->fetchOneAssociative($table, $fields, $criteria, $order_by);
        if (null === $row) {
            return null;
        }

        return Vec\values($row);
    }

    /**
     * Fetch one, or more row from the given table, where columns are index using their names.
     *
     * Example:
     *
     * ```php
     *  $articles = $database->fetchAll('articles', ['title', 'content'], criteria: ['author' => 'azjezz'], limit: 10);
     *  foreach($articles as $article) {
     *      ['title' => $title, 'content' => $content] = $article;
     *      // do something...
     *  }
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<non-empty-string, mixed> $criteria
     * @param int<0, max>|null $offset
     * @param int<0, max>|null $limit
     * @param array<non-empty-string, OrderDirection> $order_by
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, $order_by contains invalid values, or $offset, or $limit are negative.
     *
     * @return list<array<string, mixed>>
     */
    public function fetchAllAssociative(string $table, array $fields = ['*'], array $criteria = [], ?int $offset = null, ?int $limit = null, array $order_by = []): array
    {
        [$query, $values] = $this->buildCriteria(
            $this->createQueryBuilder()->select(...$fields)->from($table),
            $criteria
        );

        foreach ($order_by as $sort => $direction) {
            $query = $query->andOrderBy($sort, $direction);
        }

        if ($limit !== null) {
            $query = $query->limit($limit);

            if ($offset !== null) {
                $query = $query->offset($offset);
            }
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return $query->fetchAllAssociative($values);
    }

    /**
     * Fetch one, or more row from the given table, where columns are index using numeric values.
     *
     * Example:
     *
     * ```php
     *  $articles = $database->fetchAllNumeric('articles', ['title', 'content'], criteria: ['author' => 'azjezz'], offset: 2, limit: 10);
     *  foreach($articles as $article) {
     *      [$title, $content] = $article;
     *      // do something...
     *  }
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<non-empty-string, mixed> $criteria
     * @param int<0, max>|null $offset
     * @param int<0, max>|null $limit
     * @param array<non-empty-string, OrderDirection> $orderBy
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, $order_by contains invalid values, or $offset, or $limit are negative.
     *
     * @return list<list<mixed>>
     */
    public function fetchAllNumeric(string $table, array $fields = ['*'], array $criteria = [], ?int $offset = null, ?int $limit = null, array $orderBy = []): array
    {
        return Vec\map(
            $this->fetchAllAssociative($table, $fields, $criteria, $offset, $limit, $orderBy),
            static fn($row) => Vec\values($row)
        );
    }

    /**
     * @template T of Query\WhereQueryInterface
     *
     * @param T $query
     * @param array<non-empty-string, mixed> $criteria
     * @param array<non-empty-string, mixed> $values
     *
     * @return array{T, array<non-empty-string, mixed>}
     */
    private function buildCriteria(Query\WhereQueryInterface $query, array $criteria, array $values = []): array
    {
        $expr = $this->createExpressionBuilder();
        foreach ($criteria as $column => $criterion) {
            if ($criterion === null) {
                $query = $query->andWhere($expr->isNull($column));
                continue;
            }

            [$name, $placeholder] = $this->buildPlaceholder($column, 'criteria');
            $query = $query->andWhere($expr->equal($column, $placeholder));
            
            $values[$name] = $criterion;
        }

        return [$query, $values];
    }

    /**
     * @return array{non-empty-string, non-empty-string}
     */
    private function buildPlaceholder(string $column, string $prefix, int $index = 0): array
    {
        /** @var non-empty-string $placeholder */
        $placeholder = Str\format('%s_%s_%d', $prefix, $column, $index);

        return [$placeholder, ':' . $placeholder];
    }
}
