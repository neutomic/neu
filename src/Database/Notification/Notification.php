<?php

declare(strict_types=1);

namespace Neu\Database\Notification;

final class Notification
{
    /**
     * @param string $channel The channel identifier
     * @param string $payload The message payload
     * @param int<1, max> $pid The process id of the message source.
     */
    public function __construct(
        public readonly string $channel,
        public readonly string $payload,
        public readonly int $pid,
    ) {
    }
}
