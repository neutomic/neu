<?php

declare(strict_types=1);

namespace Neu\Tests\EventDispatcher\ListenerProvider;

use Neu\EventDispatcher\Listener\ListenerInterface;
use Neu\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use Neu\Tests\EventDispatcher\Fixture\Event\OrderUpdatedEvent;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class PrioritizedListenerProviderTest extends TestCase
{
    public function testAddingTheSameListenerTwiceEarlyReturns(): void
    {
        $listener = $this->createMock(ListenerInterface::class);
        $listener->expects(static::never())->method('process');

        $event = new OrderUpdatedEvent(1);

        $provider = new PrioritizedListenerProvider();
        $provider->listen($event::class, $listener);
        $provider->listen($event::class, $listener);
        $provider->listen($event::class, $listener);

        $listeners = Vec\values($provider->getListenersForEvent($event));

        static::assertCount(1, $listeners);
        static::assertSame([$listener], $listeners);
    }

    public function testAggregate(): void
    {
        $provider = new PrioritizedListenerProvider();

        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1->expects(static::never())->method('process');
        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2->expects(static::never())->method('process');
        $listener3 = $this->createMock(ListenerInterface::class);
        $listener3->expects(static::never())->method('process');

        $event = new OrderUpdatedEvent(1);

        $provider->listen($event::class, $listener1, 3);
        $provider->listen($event::class, $listener2, 0);
        $provider->listen($event::class, $listener3, 2);

        $listeners = Vec\values($provider->getListenersForEvent($event));

        static::assertCount(3, $listeners);
        static::assertSame([$listener2, $listener3, $listener1,], $listeners);
    }
}
