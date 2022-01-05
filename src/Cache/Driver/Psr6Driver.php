<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

use Neu\Cache\Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

final class Psr6Driver implements DriverInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $pool
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
            $item = $this->pool->getItem($key);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }

        if (!$item->isHit()) {
            throw Exception\UnavailableItemException::for($key);
        }

        return $item->get();
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
            $item = $this->pool->getItem($key);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }

        $item->set($value);
        $item->expiresAfter($ttl);

        $this->pool->save($item);
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
            $this->pool->deleteItem($key);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidKeyException($e->getMessage(), previous: $e);
        }
    }
}
