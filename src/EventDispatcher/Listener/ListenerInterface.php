<?php

declare(strict_types=1);

namespace Neu\EventDispatcher\Listener;

/**
 * @template T of object
 */
interface ListenerInterface
{
    /**
     * Process the given event.
     *
     * @param T $event The event object to process.
     *
     * @return T The event object that was passed, now modified by the listener.
     */
    public function process(object $event): object;
}
