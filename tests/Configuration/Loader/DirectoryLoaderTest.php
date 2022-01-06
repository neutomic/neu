<?php

declare(strict_types=1);

namespace Neu\Tests\Configuration\Loader;

use Neu\Configuration\Exception\LogicException;
use Neu\Configuration\Loader\DirectoryLoader;
use Neu\Configuration\Loader\JsonFileLoader;
use Neu\Configuration\Loader\PHPFileLoader;
use Neu\Configuration\Resolver\Resolver;
use PHPUnit\Framework\TestCase;

final class DirectoryLoaderTest extends TestCase
{
    public function testLoadFile(): void
    {
        $loader = new DirectoryLoader();
        $resolver = new Resolver([new PHPFileLoader(), new JsonFileLoader(), $loader]);

        $resource = __DIR__ . '/../Resources/config/';

        static::assertSame($loader, $resolver->resolve($resource));

        $configuration = $resolver->resolve($resource)->load($resource);

        static::assertTrue($configuration->has('foo'));
        static::assertSame(['bar' => [true, true], 'baz' => [false, false]], $configuration->get('foo'));
        static::assertTrue($configuration->has('format'));
        static::assertCount(2, $configuration->get('format'));
        static::assertContains('php', $configuration->get('format'));
        static::assertContains('json', $configuration->get('format'));
    }

    public function testLoadWithoutResolver(): void
    {
        $loader = new DirectoryLoader();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Resolver has not been set on the "' . DirectoryLoader::class . '" loader, make sure to call "' . DirectoryLoader::class . '::setResolver()" before attempting to load resources.'
        );

        $loader->load(__DIR__ . '/../Resources/config/');
    }

    /**
     * @dataProvider getSupportCases
     */
    public function testSupport(mixed $resource, bool $supported): void
    {
        $loader = new DirectoryLoader();

        static::assertSame($supported, $loader->supports($resource));
    }

    public function getSupportCases(): iterable
    {
        return [
            [__DIR__, true],
            [__FILE__, false],
            ['file.php', false],
            ['file.php5', false],
            ['file.php7', false],
            ['file.json', false],
            ['file.js', false],
            ['file.yaml', false],
            ['', false],
            [[], false],
            [false, false],
            [null, false],
        ];
    }
}
