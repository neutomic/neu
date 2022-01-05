<?php

declare(strict_types=1);

namespace Neu\Cache;

use Closure;
use Psl\Async;

final class Cache implements CacheInterface
{
    /**
     * @var Async\KeyedSequence<string, array{0: Closure(): mixed, 1: null|positive-int}, mixed>
     */
    private Async\KeyedSequence $sequence;

    public function __construct(private readonly Driver\DriverInterface $driver)
    {
        $this->sequence = new Async\KeyedSequence(
            /**
             * @param array{0: Closure(): mixed, 1: null|positive-int} $input
             *
             * @return mixed
             */
            function (string $key, array $input): mixed {
                /** @var non-empty-string $key */
                try {
                    return $this->driver->get($key);
                } catch (Exception\UnavailableItemException) {
                    [$computer, $ttl] = $input;

                    /** @var mixed $value */
                    $value = $computer();

                    $this->driver->set($key, $value, $ttl);

                    return $value;
                }
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
            return $this->sequence->waitFor($key, [$computer, $ttl]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        // wait for pending operations associated with the given key.
        $this->sequence->waitForPending($key);

        $this->driver->delete($key);
    }
}
