<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

use Neu\Cache\Exception;

interface DriverInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @param non-empty-string $key The unique key of this item in the cache.
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\UnavailableItemException If $key is not present in the cache.
     *
     * @return mixed The value of the item from the cache.
     */
    public function get(string $key): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param non-empty-string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param null|positive-int $ttl The TTL value of this item.
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\InvalidKeyException If the $value cannot be stored using this driver.
     */
    public function set(string $key, mixed $value, null|int $ttl = null): void;

    /**
     * Delete an item from the cache by its unique key.
     *
     * If the value is not present within the cache, this method *MUST* return immediately.
     *
     * This method must wait until the item is deleted, rather than deferring the action.
     *
     * @param non-empty-string $key The unique cache key of the item to delete.
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     */
    public function delete(string $key): void;
}
