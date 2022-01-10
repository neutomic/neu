<?php

declare(strict_types=1);

namespace Neu\Tests\Configuration;

use Closure;
use Neu\Configuration\Container;
use Neu\Configuration\ContainerInterface;
use Neu\Configuration\Exception\InvalidEntryException;
use Neu\Configuration\Exception\MissingEntryException;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testHas(): void
    {
        $configuration = new Container([
            'foo' => null,
            'bar' => false,
            'baz' => []
        ]);

        static::assertTrue($configuration->has('foo'));
        static::assertTrue($configuration->has('bar'));
        static::assertTrue($configuration->has('baz'));
        static::assertFalse($configuration->has('qux'));
    }

    public function testGet(): void
    {
        $configuration = new Container([
            'foo' => $this,
        ]);

        static::assertSame($this, $configuration->get('foo'));
    }

    public function testGetThrowsForUndefinedEntries(): void
    {
        $configuration = new Container([]);

        $this->expectException(MissingEntryException::class);
        $this->expectExceptionMessage('Entry "foo" does not exist within the container.');

        $configuration->get('foo');
    }

    public function testTypedGetters(): void
    {
        $configuration = new Container([
            'foo' => '12',
            'bar' => 'false',
            'baz' => 'true',
            'qux' => '1',
            'quxx' => [
                'foo' => [1 => 'foo', 'two' => 'bar'],
                'bar' => [1, 2, 3],
                'baz' => ['a', 'b', 'c'],
            ],
        ]);

        static::assertSame(12, $configuration->int('foo'));
        static::assertSame(12.0, $configuration->float('foo'));
        static::assertSame('12', $configuration->string('foo'));
        static::assertFalse($configuration->bool('bar'));
        static::assertTrue($configuration->bool('baz'));
        static::assertTrue($configuration->bool('qux'));

        static::assertSame([1, 'two'], $configuration->document('quxx')->container('foo')->indices());
        static::assertSame([1 => 'foo', 'two' => 'bar'], $configuration->document('quxx')->container('foo')->all());

        static::assertSame([0, 1, 2], $configuration->document('quxx')->list('bar')->indices());
        static::assertSame([1, 2, 3], $configuration->document('quxx')->list('bar')->all());

        static::assertSame([0, 1, 2], $configuration->document('quxx')->list('baz')->indices());
        static::assertSame(['a', 'b', 'c'], $configuration->document('quxx')->list('baz')->all());
    }

    /**
     * @dataProvider provideInvalidGetOperations
     */
    public function testInvalidGetOperations(array $entries, Closure $operation, string $message): void
    {
        $container = new Container($entries);

        $this->expectException(InvalidEntryException::class);
        $this->expectExceptionMessage($message);

        $operation($container);
    }

    /**
     * @return iterable<array{array, Closure(ContainerInterface): mixed, string}>
     */
    public function provideInvalidGetOperations(): iterable
    {
        yield [
            ['foo' => [1, 2, 3]],
            static fn(ContainerInterface $container) => $container->string('foo'),
            'Entry "foo" value cannot be converted into a string.'
        ];

        yield [
            ['foo' => [1, 2, 3]],
            static fn(ContainerInterface $container) => $container->int('foo'),
            'Entry "foo" value cannot be converted into an integer.'
        ];

        yield [
            ['foo' => [1, 2, 3]],
            static fn(ContainerInterface $container) => $container->float('foo'),
            'Entry "foo" value cannot be converted into a float.'
        ];

        yield [
            ['foo' => 'hello'],
            static fn(ContainerInterface $container) => $container->bool('foo'),
            'Entry "foo" value cannot be converted into a boolean.'
        ];

        yield [
            ['foo' => 'hello'],
            static fn(ContainerInterface $container) => $container->container('foo'),
            'Entry "foo" value cannot be converted into a container.'
        ];

        yield [
            ['foo' => 'hello'],
            static fn(ContainerInterface $container) => $container->document('foo'),
            'Entry "foo" value cannot be converted into a document.'
        ];

        yield [
            ['foo' => 'hello'],
            static fn(ContainerInterface $container) => $container->list('foo'),
            'Entry "foo" value cannot be converted into a list.'
        ];
    }

    public function testMerge(): void
    {
        $configuration1 = new Container(['foo' => '12']);
        $configuration2 = new Container(['bar' => '13']);
        $configuration3 = $configuration1->merge($configuration2);

        static::assertNotSame($configuration3, $configuration1);
        static::assertNotSame($configuration3, $configuration2);

        static::assertTrue($configuration1->has('foo'));
        static::assertFalse($configuration1->has('bar'));
        static::assertFalse($configuration2->has('foo'));
        static::assertTrue($configuration2->has('bar'));
        static::assertTrue($configuration3->has('foo'));
        static::assertTrue($configuration3->has('bar'));

        static::assertSame(['foo' => '12'], $configuration1->all());
        static::assertSame(['bar' => '13'], $configuration2->all());
        static::assertSame(['foo' => '12', 'bar' => '13'], $configuration3->all());
    }

    public function testMergeRecursive(): void
    {
        $configuration1 = new Container(['foo' => ['12'], 'bar' => ['qux' => ['1']]]);
        $configuration2 = new Container(['bar' => ['baz' => '2', 'qux' => ['2']]]);
        $configuration3 = $configuration1->merge($configuration2);

        static::assertNotSame($configuration3, $configuration1);
        static::assertNotSame($configuration3, $configuration2);

        static::assertTrue($configuration1->has('foo'));
        static::assertTrue($configuration1->has('bar'));
        static::assertFalse($configuration2->has('foo'));
        static::assertTrue($configuration2->has('bar'));
        static::assertTrue($configuration3->has('foo'));
        static::assertTrue($configuration3->has('bar'));

        static::assertSame(['foo' => ['12'], 'bar' => ['qux' => ['1']]], $configuration1->all());
        static::assertSame(['bar' => ['baz' => '2', 'qux' => ['2']]], $configuration2->all());
        static::assertSame(['foo' => ['12'], 'bar' => ['qux' => ['1', '2'], 'baz' => '2']], $configuration3->all());
    }
}
