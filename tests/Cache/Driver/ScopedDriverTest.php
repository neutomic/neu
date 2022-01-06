<?php

declare(strict_types=1);

namespace Neu\Tests\Cache\Driver;

use Neu\Cache\Driver\DriverInterface;
use Neu\Cache\Driver\ScopedDriver;
use PHPUnit\Framework\TestCase;

final class ScopedDriverTest extends TestCase
{
    public function testKeyIsPrefixedWithScope(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $scoped = new ScopedDriver('foo', $driver);

        $driver->expects(static::once())->method('get')->with('foo:bar')->willReturn('hello');
        $driver->expects(static::once())->method('set')->with('foo:baz', 'hello', null);
        $driver->expects(static::once())->method('delete')->with('foo:qux');

        static::assertSame('hello', $scoped->get('bar'));

        $scoped->set('baz', 'hello', null);

        $scoped->delete('qux');
    }
}
