<?php

declare(strict_types=1);

namespace Neu\Cache;

use Closure;

/**
 * An interface that describes a cache implementation.
 *
 * All implementations must be atomic.
 */
interface CacheInterface
{
    /**
     * Gets a value associated with the given key.
     *
     * If the specified key doesn't exist, `$computer` will be used to compute the value.
     *
     * @template T
     *
     * @param non-empty-string $key
     * @param Closure(): T $computer
     * @param positive-int|null $ttl
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\InvalidValueException If the value return from $computer cannot be stored in cache.
     *
     * @return T
     */
    public function compute(string $key, Closure $computer, ?int $ttl = null): mixed;

    /**
     * Update the value associated with the unique key.
     *
     * Unlike {@see compute()}, `$computer` will always be invoked to compute the value.
     *
     * The resulted value will be stored in cache, and returned as a result of this method.
     *
     * If `$key` doesn't exist in cache, it will be set.
     *
     * @template T
     *
     * @param non-empty-string $key
     * @param Closure(): T $computer
     * @param positive-int|null $ttl
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\InvalidValueException If the value return from $computer cannot be stored in cache.
     *
     * @return T
     */
    public function update(string $key, Closure $computer, ?int $ttl = null): mixed;

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param non-empty-string $key The unique cache key of the item to delete.
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     */
    public function delete(string $key): void;
}
