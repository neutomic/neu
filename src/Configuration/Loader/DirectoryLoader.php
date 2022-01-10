<?php

declare(strict_types=1);

namespace Neu\Configuration\Loader;

use Neu\Configuration\Container;
use Neu\Configuration\ContainerInterface;
use Neu\Configuration\Exception\LogicException;
use Psl\Filesystem;
use Psl\Type;

/**
 * @implements ResolverAwareLoaderInterface<non-empty-string>
 */
final class DirectoryLoader implements ResolverAwareLoaderInterface
{
    use ResolverAwareLoaderTrait;

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver has not been set.
     * @throws Filesystem\Exception\ExceptionInterface If failed to read the directory.
     */
    public function load(mixed $resource): ContainerInterface
    {
        /** @var Container<array-key> $container */
        $container = new Container([]);
        $resolver = $this->getResolver();
        foreach (Filesystem\read_directory($resource) as $node) {
            if (Filesystem\is_file($node)) {
                $container = $container->merge($resolver->resolve($node)->load($node));
            } else {
                $container = $container->merge($this->load($node));
            }
        }

        return $container;
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource): bool
    {
        if (!Type\non_empty_string()->matches($resource)) {
            return false;
        }

        if (!Filesystem\is_directory($resource)) {
            return false;
        }

        return Filesystem\is_readable($resource);
    }
}
