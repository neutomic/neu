<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

use Neu\Cache\Exception;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

final class Psr16Driver implements DriverInterface
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): mixed
    {
        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        try {
            if (!$this->cache->has($key)) {
                throw Exception\UnavailableItemException::for($key);
            }

            return $this->cache->get($key);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        if ($ttl !== null && 0 >= $ttl) {
            return;
        }

        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        try {
            $this->cache->set($key, $value, $ttl);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        try {
            $this->cache->delete($key);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }
}
