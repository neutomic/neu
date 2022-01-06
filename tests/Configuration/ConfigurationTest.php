<?php

declare(strict_types=1);

namespace Neu\Tests\Configuration;

use Neu\Configuration\Configuration;
use Neu\Configuration\Exception\InvalidEntryException;
use Neu\Configuration\TypeCoercer\TypeCoercer;
use PHPUnit\Framework\TestCase;
use Psl\Type;

final class ConfigurationTest extends TestCase
{
    public function testHas(): void
    {
        $configuration = new Configuration([
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
        $configuration = new Configuration([
            'foo' => $this,
        ]);

        static::assertSame($this, $configuration->get('foo'));
    }

    public function testGetThrowsForUndefinedEntries(): void
    {
        $configuration = new Configuration([]);

        $this->expectException(InvalidEntryException::class);
        $this->expectExceptionMessage('Entry "foo" does not exist within the configuration.');

        $configuration->get('foo');
    }

    public function testGetTyped(): void
    {
        $configuration = new Configuration(['foo' => '12']);

        static::assertSame(12, $configuration->getTyped('foo', TypeCoercer::of(Type\positive_int())));
    }

    public function testGetTypedThrowsForInvalidTyped(): void
    {
        $configuration = new Configuration(['foo' => '12']);

        $this->expectException(InvalidEntryException::class);

        $configuration->getTyped('foo', TypeCoercer::of(Type\null()));
    }

    public function testMerge(): void
    {
        $configuration1 = new Configuration(['foo' => '12']);
        $configuration2 = new Configuration(['bar' => '13']);
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
        $configuration1 = new Configuration(['foo' => ['12'], 'bar' => ['qux' => ['1']]]);
        $configuration2 = new Configuration(['bar' => ['baz' => '2', 'qux' => ['2']]]);
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
