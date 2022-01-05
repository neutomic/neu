<?php

declare(strict_types=1);

namespace Neu\EventDispatcher\ListenerProvider;

use Neu\EventDispatcher\Listener;
use Psl\Iter;

use function is_subclass_of;

final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<class-string, list<Listener\ListenerInterface<object>>>
     */
    private array $listeners = [];

    /**
     * @template T
     *
     * @param class-string<T> $event
     * @param Listener\ListenerInterface<T> $listener
     */
    public function listen(string $event, Listener\ListenerInterface $listener): self
    {
        $listeners = $this->listeners[$event] ?? [];
        if (Iter\contains($listeners, $listener)) {
            return $this;
        }

        $listeners[] = $listener;
        $this->listeners[$event] = $listeners;

        return $this;
    }

    /**
     * @template T of object
     *
     * @param T $event An event for which to return the relevant listeners.
     *
     * @return iterable<Listener\ListenerInterface<T>> An iterable (array, iterator, or generator) of callables.
     *                                                 Each callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->listeners as $event_type => $listeners) {
            if ($event::class === $event_type || is_subclass_of($event::class, $event_type)) {
                foreach ($listeners as $listener) {
                    /** @var Listener\ListenerInterface<T> */
                    yield $listener;
                }
            }
        }
    }
}
