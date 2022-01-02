<?php

declare(strict_types=1);

namespace Neu\EventDispatcher\ListenerProvider;

use Neu\EventDispatcher\Listener;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

use function is_subclass_of;

final class PrioritizedListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<string, array<class-string, list<Listener\ListenerInterface<object>>>>
     */
    private array $listeners = [];

    /**
     * @template T
     *
     * @param class-string<T> $event
     * @param Listener\ListenerInterface<T> $listener
     */
    public function listen(string $event, Listener\ListenerInterface $listener, int $priority = 1): self
    {
        $priority = Str\format('%d.0', $priority);
        if (isset($this->listeners[$priority][$event]) && Iter\contains($this->listeners[$priority][$event], $listener)) {
            return $this;
        }

        $priority_listeners = $this->listeners[$priority] ?? [];
        $event_listeners = $priority_listeners[$event] ?? [];
        $event_listeners[] = $listener;
        $priority_listeners[$event] = $event_listeners;
        $this->listeners[$priority] = $priority_listeners;

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
        $priorities = Vec\sort(
            Vec\keys($this->listeners),
            static fn(string $a, string $b): int => $a <=> $b,
        );

        foreach ($priorities as $priority) {
            foreach ($this->listeners[$priority] as $event_type => $listeners) {
                if ($event::class === $event_type || is_subclass_of($event::class, $event_type)) {
                    foreach ($listeners as $listener) {
                        /** @var Listener\ListenerInterface<T> */
                        yield $listener;
                    }
                }
            }
        }
    }
}
