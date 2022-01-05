<?php

declare(strict_types=1);

namespace Neu\Tests\EventDispatcher\ListenerProvider;

use Neu\EventDispatcher\Listener\ListenerInterface;
use Neu\EventDispatcher\ListenerProvider\ListenerProvider;
use Neu\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Neu\Tests\EventDispatcher\Fixture\Event\OrderUpdatedEvent;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ListenerProviderAggregateTest extends TestCase
{
    public function testAggregate(): void
    {
        $aggregate = new ListenerProviderAggregate();

        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1->expects(static::never())->method('process');
        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2->expects(static::never())->method('process');
        $listener3 = $this->createMock(ListenerInterface::class);
        $listener3->expects(static::never())->method('process');

        $event = new OrderUpdatedEvent(1);

        $provider1 = new ListenerProvider();
        $provider1->listen($event::class, $listener1);
        $provider2 = new ListenerProvider();
        $provider2->listen($event::class, $listener2);
        $provider3 = new ListenerProvider();
        $provider3->listen($event::class, $listener3);

        $aggregate->attach($provider1)->attach($provider2)->attach($provider3);

        $listeners = Vec\values($aggregate->getListenersForEvent($event));

        static::assertCount(3, $listeners);
        static::assertSame([$listener1, $listener2, $listener3], $listeners);
    }
}
