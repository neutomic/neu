<?php

declare(strict_types=1);

namespace Neu\Configuration\Loader;

use Neu\Configuration\Resolver\ResolverInterface;

/**
 * @template T
 *
 * @extends LoaderInterface<T>
 */
interface ResolverAwareLoaderInterface extends LoaderInterface
{
    /**
     * Set the resolver to be used for loading sub-resources.
     */
    public function setResolver(ResolverInterface $resolver): void;
}
