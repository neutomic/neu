<?php

declare(strict_types=1);

namespace Neu\Tests\Cache\Driver;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Neu\Cache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class Psr16DriverTest extends TestCase
{
    public function testInvalidKey(): void
    {
        $driver = new Cache\Driver\Psr16Driver(new ArrayCachePool());
        try {
            $driver->set('', 'foo');
            static::fail('Expected exception to be thrown');
        } catch (Cache\Exception\InvalidKeyException $e) {
            $this->addToAssertionCount(1);
            static::assertSame('Cache key must not be empty.', $e->getMessage());
        }

        try {
            $driver->get('');
            static::fail('Expected exception to be thrown');
        } catch (Cache\Exception\InvalidKeyException) {
            $this->addToAssertionCount(1);
        }

        try {
            $driver->delete('');
            static::fail('Expected exception to be thrown');
        } catch (Cache\Exception\InvalidKeyException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(Cache\Exception\InvalidKeyException::class);

        $driver->get('{framework}');
    }

    public function testSetGetDelete(): void
    {
        $driver = new Cache\Driver\Psr16Driver(new Psr16Cache(new ArrayAdapter()));

        $driver->set('user', 'azjezz');
        static::assertSame('azjezz', $driver->get('user'));
        $driver->set('user', 'trowski');
        static::assertSame('trowski', $driver->get('user'));
        $driver->delete('user');

        try {
            $driver->get('user');
            static::fail('Expected exception to be thrown.');
        } catch (Cache\Exception\UnavailableItemException) {
            $this->addToAssertionCount(1);
        }

        $driver->set('user', 'azjezz', 0);
        $this->expectException(Cache\Exception\UnavailableItemException::class);
        $driver->get('user');
    }
}
