<?php

declare(strict_types=1);

namespace Neu\EventDispatcher\ListenerProvider;

use Neu\EventDispatcher\Listener;

final class ListenerProviderAggregate implements ListenerProviderInterface
{
    /**
     * @param list<ListenerProviderInterface> $providers
     */
    public function __construct(
        private array $providers = [],
    ) {
    }

    public function attach(ListenerProviderInterface $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @template T of object
     *
     * @param T $event An event for which to return the relevant listeners.
     *
     * @return iterable<Listener\ListenerInterface<T>>
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->providers as $provider) {
            foreach ($provider->getListenersForEvent($event) as $listener) {
                /** @var Listener\ListenerInterface<T> */
                yield $listener;
            }
        }
    }
}
