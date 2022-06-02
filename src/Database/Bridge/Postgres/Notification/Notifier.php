<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres\Notification;

use Amp\Postgres\Executor;
use Amp\Sql\ConnectionException;
use Amp\Sql\SqlException;
use Neu\Database\Bridge\Postgres\QueryResult;
use Neu\Database\Exception;
use Neu\Database\Notification\NotifierInterface;
use Neu\Database\QueryResultInterface;

final class Notifier implements NotifierInterface
{
    /**
     * @param non-empty-string $channel
     */
    public function __construct(
        private readonly Executor $executor,
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
    public function notify(string $message = ''): QueryResultInterface
    {
        try {
            $result = $this->executor->notify($this->channel, $message);
        } catch (ConnectionException $e) {
            throw new Exception\ConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return new QueryResult($result);
    }
}
