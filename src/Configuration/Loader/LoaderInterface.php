<?php

declare(strict_types=1);

namespace Neu\Configuration\Loader;

use Neu\Configuration\ContainerInterface;
use Neu\Configuration\Exception;

/**
 * @template T
 */
interface LoaderInterface
{
    /**
     * Load the given resource.
     *
     * @param T $resource
     *
     * @throws Exception\InvalidConfigurationException If loading the resource resulted in an invalid configuration value.
     */
    public function load(mixed $resource): ContainerInterface;

    /**
     * Return whether this loader is capable of loading the given resource.
     *
     * @param mixed $resource
     *
     * @psalm-assert-if-true T $resource
     */
    public function supports(mixed $resource): bool;
}
