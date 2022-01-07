<?php

declare(strict_types=1);

namespace Neu\Configuration\Resolver;

use Neu\Configuration\Exception;
use Neu\Configuration\Loader\LoaderInterface;
use Neu\Configuration\Loader\ResolverAwareLoaderInterface;

interface ResolverInterface
{
    /**
     * Retrieve a loader capable to loading the given resource.
     *
     * If the loader implements {@see ResolverAwareLoaderInterface},
     * {@see ResolverAwareLoaderInterface::setResolver()} must be called with the current resolver.
     *
     * @template T
     *
     * @param T $resource
     *
     * @throws Exception\NoSupportiveLoaderException If no supportive loader is found.
     *
     * @return LoaderInterface<T>
     */
    public function resolve(mixed $resource): LoaderInterface;
}
