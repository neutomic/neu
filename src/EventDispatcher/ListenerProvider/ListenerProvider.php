<?php

declare(strict_types=1);

namespace Neu\EventDispatcher\ListenerProvider;

use Generator;
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
     * @return Iter\Iterator<int, Listener\ListenerInterface<T>> An Iterator or listeners.
     */
    public function getListenersForEvent(object $event): Iter\Iterator
    {
        /** @var Iter\Iterator<int, Listener\ListenerInterface<T>> */
        return Iter\Iterator::from(
            /**
             * @return Generator<int, Listener\ListenerInterface<object>, mixed, void>
             */
            function () use ($event): Generator {
                foreach ($this->listeners as $event_type => $listeners) {
                    if ($event::class === $event_type || is_subclass_of($event::class, $event_type)) {
                        foreach ($listeners as $listener) {
                            yield $listener;
                        }
                    }
                }
            }
        );
    }
}
