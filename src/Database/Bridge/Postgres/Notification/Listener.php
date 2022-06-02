<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres\Notification;

use Amp\Postgres\Listener as AmphpListener;
use Iterator;
use Neu\Database\Notification\ListenerInterface;
use Neu\Database\Notification\Notification;

final class Listener implements ListenerInterface
{
    /**
     * @param non-empty-string $channel
     */
    public function __construct(
        private readonly AmphpListener $listener,
        private readonly string $channel,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * {@inheritDoc}
     */
    public function isAlive(): bool
    {
        return $this->listener->isListening();
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->listener->unlisten();
    }

    /**
     * {@inheritDoc}
     */
    public function listen(): Iterator
    {
        foreach ($this->listener as $notification) {
            yield new Notification($notification->channel, $notification->payload, $notification->pid);
        }
    }
}
