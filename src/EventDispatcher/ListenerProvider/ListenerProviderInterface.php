<?php

declare(strict_types=1);

namespace Neu\EventDispatcher\ListenerProvider;

use Neu\EventDispatcher\Listener;
use Psr\EventDispatcher;

interface ListenerProviderInterface extends EventDispatcher\ListenerProviderInterface
{
    /**
     * @template T of object
     *
     * @param T $event An event for which to return the relevant listeners.
     *
     * @return iterable<Listener\ListenerInterface<T>> An iterable (array, iterator, or generator) of listeners.
     */
    public function getListenersForEvent(object $event): iterable;
}
