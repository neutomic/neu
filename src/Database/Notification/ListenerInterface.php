<?php

declare(strict_types=1);

namespace Neu\Database\Notification;

use Iterator;

interface ListenerInterface
{
    /**
     * Retrieve the channel name that is used for receiving notifications.
     *
     * @return non-empty-string
     */
    public function getChannel(): string;

    /**
     * Check whether the listener is still able to receive notifications.
     */
    public function isAlive(): bool;

    /**
     * Close the listener.
     *
     * After the listener is closed, no more notifications will be received.
     */
    public function close(): void;

    /**
     * @return Iterator<int, Notification>
     */
    public function listen(): Iterator;
}
