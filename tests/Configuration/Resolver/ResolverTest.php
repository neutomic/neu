<?php

declare(strict_types=1);

namespace Neu\Tests\Configuration\Resolver;

use Neu\Configuration\Exception\NoSupportiveLoaderException;
use Neu\Configuration\Loader\LoaderInterface;
use Neu\Configuration\Loader\ResolverAwareLoaderInterface;
use Neu\Configuration\Resolver\Resolver;
use PHPUnit\Framework\TestCase;

final class ResolverTest extends TestCase
{
    public function testResolving(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(LoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('foo.json')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('foo.json')->willReturn(true);

        $loader = $resolver->resolve('foo.json');

        static::assertSame($loader2, $loader);
    }

    public function testResolvingWithNoSupportiveLoaders(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(LoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('file.yaml')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('file.yaml')->willReturn(false);

        $this->expectException(NoSupportiveLoaderException::class);
        $this->expectExceptionMessage('Unable to load resource "file.yaml": no supportive loader found.');

        $resolver->resolve('file.yaml');
    }

    public function testResolvingComplexResourceWithNoSupportiveLoaders(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(LoaderInterface::class);

        $resolver = new Resolver([]);
        $resolver->addLoader($loader1);
        $resolver->addLoader($loader2);

        $loader1->expects(static::once())->method('supports')->with([])->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with([])->willReturn(false);

        $this->expectException(NoSupportiveLoaderException::class);
        $this->expectExceptionMessage('Unable to load resource "{array}": no supportive loader found.');

        $resolver->resolve([]);
    }

    public function testResolvingWithResolverAwareLoader(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(ResolverAwareLoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('foo.json')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('foo.json')->willReturn(true);
        $loader2->expects(static::once())->method('setResolver')->with($resolver);

        $loader = $resolver->resolve('foo.json');

        static::assertSame($loader2, $loader);
    }

    public function testSetResolverIsNotCalledOnNonSupportiveLoaders(): void
    {
        $loader1 = $this->createMock(ResolverAwareLoaderInterface::class);
        $loader2 = $this->createMock(ResolverAwareLoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('foo.json')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('foo.json')->willReturn(true);
        $loader1->expects(static::never())->method('setResolver');
        $loader2->expects(static::once())->method('setResolver')->with($resolver);

        $loader = $resolver->resolve('foo.json');

        static::assertSame($loader2, $loader);
    }
}
