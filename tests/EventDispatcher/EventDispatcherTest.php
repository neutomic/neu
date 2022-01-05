<?php

declare(strict_types=1);

namespace Neu\Tests\EventDispatcher;

use Amp;
use Neu\EventDispatcher\EventDispatcher;
use Neu\EventDispatcher\Listener\ListenerInterface;
use Neu\EventDispatcher\ListenerProvider\ListenerProvider;
use Neu\Tests\EventDispatcher\Fixture\Event\OrderCreatedEvent;
use Neu\Tests\EventDispatcher\Fixture\Event\OrderUpdatedEvent;
use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;

final class EventDispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new OrderCreatedEvent(1);
        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1
            ->expects(static::once())
            ->method('process')
            ->with($event)
            ->willReturn($event);

        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2
            ->expects(static::once())
            ->method('process')
            ->with($event)
            ->willReturn($event);

        $provider = new ListenerProvider();
        $provider->listen(OrderCreatedEvent::class, $listener1);
        $provider->listen(OrderCreatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($provider);

        $returned = $dispatcher->dispatch($event);

        static::assertSame($returned, $event);
    }

    public function testDispatchListenerStopsEvent(): void
    {
        $event = new OrderUpdatedEvent(1);
        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1
            ->expects(static::once())
            ->method('process')
            ->with($event)
            ->willReturnCallback(static function (OrderUpdatedEvent $event): OrderUpdatedEvent {
                $event->stopped = true;

                return $event;
            });

        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2->expects(static::never())->method('process');

        $provider = new ListenerProvider();
        $provider->listen(OrderUpdatedEvent::class, $listener1);
        $provider->listen(OrderUpdatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($provider);

        $returned = $dispatcher->dispatch($event);

        static::assertSame($returned, $event);
    }

    public function testDispatchStoppedEvent(): void
    {
        $event = new OrderUpdatedEvent(1);
        $event->stopped = true;

        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1->expects(static::never())->method('process');
        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2->expects(static::never())->method('process');

        $provider = new ListenerProvider();
        $provider->listen(OrderUpdatedEvent::class, $listener1);
        $provider->listen(OrderUpdatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($provider);

        $returned = $dispatcher->dispatch($event);

        static::assertSame($returned, $event);
    }

    public function testDispatchingTheSameEventConcurrently(): void
    {
        $ref = new Psl\Ref('');
        $event = new OrderUpdatedEvent(1);
        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1
            ->expects(static::exactly(2))
            ->method('process')
            ->with($event)
            ->willReturnCallback(static function (OrderUpdatedEvent $event) use ($ref): OrderUpdatedEvent {
                $event->orderId++;
                $ref->value .= '1';

                Async\sleep(0.02);

                return $event;
            });

        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2
            ->expects(static::exactly(2))
            ->method('process')
            ->with($event)
            ->willReturnCallback(static function (OrderUpdatedEvent $event) use ($ref): OrderUpdatedEvent {
                $event->orderId++;
                $ref->value .= '2';

                Async\sleep(0.02);

                return $event;
            });

        $provider = new ListenerProvider();
        $provider->listen(OrderUpdatedEvent::class, $listener1);
        $provider->listen(OrderUpdatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($provider);

        [$one, $two] = Amp\Future\all([
            Amp\async(static fn() =>  $dispatcher->dispatch($event)),
            Amp\async(static fn() =>  $dispatcher->dispatch($event)),
        ]);

        static::assertSame($one, $event);
        static::assertSame($two, $event);
        static::assertSame('1212', $ref->value);
    }
}
