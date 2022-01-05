<?php

declare(strict_types=1);

namespace Neu\Tests\EventDispatcher\Fixture\Event;

final class OrderCreatedEvent
{
    public function __construct(
        public readonly int $orderId
    ) {
    }
}
