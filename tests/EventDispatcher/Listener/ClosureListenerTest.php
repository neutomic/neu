<?php

declare(strict_types=1);

namespace Neu\Tests\EventDispatcher\Listener;

use Neu\EventDispatcher\Listener\ClosureListener;
use Neu\Tests\EventDispatcher\Fixture\Event\OrderUpdatedEvent;
use PHPUnit\Framework\TestCase;

final class ClosureListenerTest extends TestCase
{
    public function testProcess(): void
    {
        $listener = new ClosureListener(static function (OrderUpdatedEvent $event): OrderUpdatedEvent {
            static::assertSame(1, $event->orderId);

            return $event;
        });

        $event = new OrderUpdatedEvent(1);
        $return = $listener->process($event);

        static::assertSame($return, $event);
    }
}
