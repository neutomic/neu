<?php

declare(strict_types=1);

namespace Neu\EventDispatcher\Listener;

use Closure;

/**
 * @template T of object
 *
 * @implements ListenerInterface<T>
 */
final class ClosureListener implements ListenerInterface
{
    /**
     * @param Closure(T): T $closure
     */
    public function __construct(
        private readonly Closure $closure
    ) {
    }

    public function process(object $event): object
    {
        return ($this->closure)($event);
    }
}
