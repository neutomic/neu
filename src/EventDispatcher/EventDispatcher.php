<?php

declare(strict_types=1);

namespace Neu\EventDispatcher;

use Psl\Async;
use Psr\EventDispatcher\StoppableEventInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var Async\KeyedSequence<class-string, object, object>
     */
    private Async\KeyedSequence $sequence;

    public function __construct(
        private readonly ListenerProvider\ListenerProviderInterface $provider
    ) {
        $this->sequence = new Async\KeyedSequence(function (string $event_type, object $event): object {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            $listeners = $this->provider->getListenersForEvent($event);
            foreach ($listeners as $listener) {
                if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                    return $event;
                }

                $event = $listener->process($event);
            }

            return $event;
        });
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @template T of object
     *
     * @param T $event The event object to process.
     *
     * @return T The event object that was passed, now modified by listeners.
     */
    public function dispatch(object $event): object
    {
        /** @var T */
        return $this->sequence->waitFor($event::class, $event);
    }
}
