<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

use Neu\Cache\Exception;
use Revolt\EventLoop;

use function array_key_exists;
use function array_key_first;
use function count;
use function time;

final class LocalDriver implements DriverInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $cache = [];
    private array $cacheExpiration = [];

    private readonly string $gcWatcher;

    /**
     * @param null|positive-int $size The maximum number of items that can be held in cache at one time.
     * @param positive-int $gcInterval The interval of which to run garbage collection to remove expired items.
     */
    public function __construct(
        private readonly ?int $size = null,
        private readonly int $gcInterval = 10,
    ) {
        $this->gcWatcher = EventLoop::repeat($this->gcInterval, $this->garbageCollection(...));

        EventLoop::unreference($this->gcWatcher);
    }

    public function __destruct()
    {
        EventLoop::disable($this->gcWatcher);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): mixed
    {
        if ('' === $key) {
            throw Exception\InvalidKeyException::forEmptyKey();
        }

        if (array_key_exists($key, $this->cache)) {
            if (!array_key_exists($key, $this->cacheExpiration)) {
                return $this->cache[$key];
            }

            if (time() < $this->cacheExpiration[$key]) {
                return $this->cache[$key];
            }

            unset($this->cache[$key]);
        }

        throw Exception\UnavailableItemException::for($key);
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

        $this->cache[$key] = $value;
        if ($ttl !== null) {
            $this->cacheExpiration[$key] = time() + $ttl;
        }

        if (null !== $this->size && count($this->cache) === $this->size) {
            $this->delete(array_key_first($this->cache));
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

        unset($this->cache[$key], $this->cacheExpiration[$key]);
    }

    private function garbageCollection(): void
    {
        foreach ($this->cacheExpiration as $key => $time) {
            if (time() >= $time) {
                $this->delete($key);
            }
        }
    }
}
