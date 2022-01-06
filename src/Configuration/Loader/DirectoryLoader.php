<?php

declare(strict_types=1);

namespace Neu\Configuration\Loader;

use Neu\Configuration\Configuration;
use Neu\Configuration\ConfigurationInterface;
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
    public function load(mixed $resource): ConfigurationInterface
    {
        $configuration = new Configuration([]);
        $resolver = $this->getResolver();
        foreach (Filesystem\read_directory($resource) as $node) {
            if (Filesystem\is_file($node)) {
                $configuration = $configuration->merge($resolver->resolve($node)->load($node));
            } else {
                $configuration = $configuration->merge($this->load($node));
            }
        }

        return $configuration;
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
