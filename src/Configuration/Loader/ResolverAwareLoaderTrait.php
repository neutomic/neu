<?php

declare(strict_types=1);

namespace Neu\Configuration\Loader;

use Neu\Configuration\Exception\LogicException;
use Neu\Configuration\Resolver\ResolverInterface;
use Psl\Str;

/**
 * @psalm-require-implements ResolverAwareLoaderInterface
 */
trait ResolverAwareLoaderTrait
{
    /**
     * The resolver instance.
     */
    private ?ResolverInterface $resolver = null;

    /**
     * @inheritDoc
     */
    public function setResolver(ResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * @throws LogicException If the resolver has not been set.
     */
    protected function getResolver(): ResolverInterface
    {
        if (null === $this->resolver) {
            throw new LogicException(Str\format(
                'Resolver has not been set on the "%s" loader, make sure to call "%s::setResolver()" before attempting to load resources.',
                static::class,
                static::class,
            ));
        }

        return $this->resolver;
    }
}
