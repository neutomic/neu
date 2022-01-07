<?php

declare(strict_types=1);

namespace Neu\Configuration\Resolver;

use Neu\Configuration\Exception\NoSupportiveLoaderException;
use Neu\Configuration\Loader\LoaderInterface;
use Neu\Configuration\Loader\ResolverAwareLoaderInterface;
use Psl\Str;

use function get_debug_type;
use function is_scalar;

final class Resolver implements ResolverInterface
{
    /**
     * @param list<LoaderInterface<mixed>> $loaders
     */
    public function __construct(
        private array $loaders = [],
    ) {
    }

    /**
     * @param LoaderInterface<mixed> $loader
     */
    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * @inheritDoc
     */
    public function resolve(mixed $resource): LoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource)) {
                if ($loader instanceof ResolverAwareLoaderInterface) {
                    $loader->setResolver($this);
                }

                return $loader;
            }
        }

        throw new NoSupportiveLoaderException(Str\format(
            'Unable to load resource "%s": no supportive loader found.',
            $this->getResourceStringRepresentation($resource),
        ));
    }

    private function getResourceStringRepresentation(mixed $resource): string
    {
        if (is_scalar($resource)) {
            return (string) $resource;
        }

        return Str\format('{%s}', get_debug_type($resource));
    }
}
