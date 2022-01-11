<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

use Neu\Cache\Exception;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

final class Psr16Driver implements DriverInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly string $scope = '',
    ) {
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        try {
            if (!$this->cache->has($this->createKey($key))) {
                throw Exception\UnavailableItemException::for($key);
            }

            return $this->cache->get($this->createKey($key));
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }

    /**
     * @inheritDoc
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
            $this->cache->set($this->createKey($key), $value, $ttl);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        try {
            $this->cache->delete($this->createKey($key));
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }

    /**
     * @param non-empty-string $key
     *
     * @return non-empty-string
     */
    private function createKey(string $key): string
    {
        return '[' . $this->scope . ']=' . $key;
    }
}
