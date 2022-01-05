<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

use Amp\Cache\Cache;
use Amp\Cache\CacheException;
use Neu\Cache\Exception;
use Psl\Str;

final class AmphpDriver implements DriverInterface
{
    /**
     * @param Cache<mixed> $cache
     */
    public function __construct(
        private readonly Cache $cache,
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

        /** @var mixed $value */
        $value = $this->cache->get($key);
        if (null === $value) {
            throw Exception\UnavailableItemException::for($key);
        }

        return $value;
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

        if (null === $value) {
            throw new Exception\InvalidValueException(Str\format('Cannot use null as a value when using "%s" driver.', self::class));
        }

        try {
            $this->cache->set($key, $value, $ttl);
        } catch (CacheException $e) {
            throw new Exception\InvalidValueException($e->getMessage(), previous: $e);
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

        $this->cache->delete($key);
    }
}
