<?php

declare(strict_types=1);

namespace Neu\Database\Notification;

use Neu\Database\Exception;
use Neu\Database\QueryResultInterface;

interface NotifierInterface
{
    /**
     * Retrieve the channel name that is used for sending notifications.
     *
     * @return non-empty-string
     */
    public function getChannel(): string;

    /**
     * Send a notification to the channel.
     *
     * @param string $message - The message payload
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     */
    public function notify(string $message = ''): QueryResultInterface;
}
