<?php

declare(strict_types=1);

namespace Neu\Cache\Driver;

final class ScopedDriver implements DriverInterface
{
    /**
     * @param non-empty-string $scope
     */
    public function __construct(
        private readonly string $scope,
        private readonly DriverInterface $driver,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        return $this->driver->get($this->createKey($key));
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $this->driver->set($this->createKey($key), $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        $this->driver->delete($this->createKey($key));
    }

    /**
     * @param non-empty-string $key
     *
     * @return non-empty-string
     */
    private function createKey(string $key): string
    {
        return $this->scope . ':' . $key;
    }
}
