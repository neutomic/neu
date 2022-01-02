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
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public function get(string $key): mixed
    {
        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        try {
            return $this->cache->get($key, static function () use ($key) {
                throw Exception\UnavailableItemException::for($key);
            });
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        if ($ttl !== null && 0 >= $ttl) {
            return;
        }

        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        try {
            $this->cache->get($key, static function (CacheItemInterface $item) use ($value, $ttl) {
                $item->expiresAfter($ttl);

                return $value;
            }, INF);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }

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
