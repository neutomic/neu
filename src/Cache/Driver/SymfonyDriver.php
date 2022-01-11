<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

use Neu\Cache\Exception;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

use const INF;

final class SymfonyDriver implements DriverInterface
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
            return $this->cache->get($this->createKey($key), static function () use ($key): never {
                throw Exception\UnavailableItemException::for($key);
            });
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
            $this->cache->get($this->createKey($key), static function (CacheItemInterface $item) use ($value, $ttl): mixed {
                $item->expiresAfter($ttl);

                return $value;
            }, INF);
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
