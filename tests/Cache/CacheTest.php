<?php

declare(strict_types=1);

namespace Neu\Tests\Cache;

use Amp\Cache\LocalCache;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Neu\Cache;
use PHPUnit\Framework\TestCase;
use Psl;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class CacheTest extends TestCase
{
    /**
     * @return iterable<array{0: Cache\Cache, 1: Cache\Driver\DriverInterface}>
     */
    public function provideCache(): iterable
    {
        $driver = new Cache\Driver\LocalDriver(100, 5);
        yield [new Cache\Cache($driver), $driver];

        $driver = new Cache\Driver\Psr6Driver(new ArrayAdapter());
        yield [new Cache\Cache($driver), $driver];

        $driver = new Cache\Driver\Psr16Driver(new Psr16Cache(new ArrayAdapter()));
        yield [new Cache\Cache($driver), $driver];

        $driver = new Cache\Driver\Psr16Driver(new ArrayCachePool());
        yield [new Cache\Cache($driver), $driver];

        $driver = new Cache\Driver\AmphpDriver(new LocalCache());
        yield [new Cache\Cache($driver), $driver];

        $driver = new Cache\Driver\SymfonyDriver(new ArrayAdapter());
        yield [new Cache\Cache($driver), $driver];
    }

    /**
     * @dataProvider provideCache
     */
    public function testAtomic(Cache\Cache $cache, Cache\Driver\DriverInterface $driver): void
    {
        $ref = new Psl\Ref(false);
        $computer = static function () use ($ref): string {
            Psl\Async\sleep(0.02);
            $ref->value = true;

            return 'azjezz';
        };

        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertFalse($ref->value);

        $driver->delete('user');

        $one = Psl\Async\run(static fn() => $cache->compute('user', $computer, ttl: 1));
        Psl\Async\later();
        $two = Psl\Async\run(static fn() => $cache->compute('user', $computer, ttl: 1));
        $user = $one->await();
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $two->await();
        static::assertSame('azjezz', $user);
        static::assertFalse($ref->value);

        $driver->delete('user');

        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);
        $ref->value = false;
        $cache->delete('user');

        $ref = new Psl\Ref('a');
        Psl\Async\Scheduler::defer(static function () use ($cache, $computer, $ref): void {
            // compute the item again.
            $user = $cache->compute('user', $computer, ttl: 1);
            self::assertSame('azjezz', $user);
            self::assertSame('b', $ref->value);
            $ref->value = 'c';
        });

        Psl\Async\later();

        static::assertSame('a', $ref->value);
        $ref->value = 'b';
        $cache->delete('user'); // will wait until defer is finished.
        static::assertSame('c', $ref->value);
    }

    /**
     * @dataProvider provideCache
     */
    public function testUpdate(Cache\Cache $cache, Cache\Driver\DriverInterface $_driver): void
    {
        $ref = new Psl\Ref(false);
        $computer = static function () use ($ref): string {
            $ref->value = true;
            Psl\Async\sleep(0.02);

            return 'azjezz';
        };

        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $cache->update('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertFalse($ref->value);
    }

    /**
     * @dataProvider provideCache
     */
    public function testDelete(Cache\Cache $cache, Cache\Driver\DriverInterface $driver): void
    {
        $cache->compute('user', function (): string {
            $this->addToAssertionCount(1);

            return 'azjezz';
        });

        $user = $cache->compute('user', static function (): string {
            self::fail('value should been in cache.');
        });

        static::assertSame('azjezz', $driver->get('user'));
        static::assertSame('azjezz', $user);

        $cache->delete('user');

        $this->expectException(Cache\Exception\UnavailableItemException::class);
        $this->expectExceptionMessage('No cache item is associated with the "user" key.');

        $driver->get('user');
    }
}
