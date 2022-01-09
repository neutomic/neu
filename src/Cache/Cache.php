<?php

declare(strict_types=1);

namespace Neu\Cache;

use Closure;
use Psl\Async;

final class Cache implements CacheInterface
{
    /**
     * @var Async\KeyedSequence<non-empty-string, array{Closure(): mixed, null|positive-int, bool}, mixed>
     */
    private Async\KeyedSequence $sequence;

    public function __construct(private readonly Driver\DriverInterface $driver)
    {
        $this->sequence = new Async\KeyedSequence(
            /**
             * @param non-empty-string $key
             * @param array{Closure(): mixed, null|positive-int, bool} $input
             *
             * @return mixed
             */
            function (string $key, array $input): mixed {
                [$computer, $ttl, $forceUpdate] = $input;
                if (!$forceUpdate) {
                    try {
                        return $this->driver->get($key);
                    } catch (Exception\UnavailableItemException) {
                    }
                }

                /** @var mixed $value */
                $value = $computer();

                $this->driver->set($key, $value, $ttl);

                return $value;
            }
        );
    }

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
    public function compute(string $key, Closure $computer, ?int $ttl = null): mixed
    {
        try {
            /** @var T */
            return $this->driver->get($key);
        } catch (Exception\UnavailableItemException) {
            /** @var T */
            return $this->sequence->waitFor($key, [$computer, $ttl, false]);
        }
    }

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
    public function update(string $key, Closure $computer, ?int $ttl = null): mixed
    {
        /** @var T */
        return $this->sequence->waitFor($key, [$computer, $ttl, true]);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        // wait for pending operations associated with the given key.
        $this->sequence->waitForPending($key);

        $this->driver->delete($key);
    }
}
