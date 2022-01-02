<?php

declare(strict_types=1);

namespace Neu\Tests\EventDispatcher\ListenerProvider;

use Neu\EventDispatcher\Listener\ListenerInterface;
use Neu\EventDispatcher\ListenerProvider\ListenerProvider;
use Neu\Tests\EventDispatcher\Fixture\Event\OrderUpdatedEvent;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ListenerProviderTest extends TestCase
{
    public function testAddingTheSameListenerTwiceEarlyReturns(): void
    {
        $listener = $this->createMock(ListenerInterface::class);
        $listener->expects(static::never())->method('process');

        $event = new OrderUpdatedEvent(1);

        $provider = new ListenerProvider();
        $provider->listen($event::class, $listener);
        $provider->listen($event::class, $listener);
        $provider->listen($event::class, $listener);

        $listeners = Vec\values($provider->getListenersForEvent($event));

        static::assertCount(1, $listeners);
        static::assertSame([$listener], $listeners);
    }
}
