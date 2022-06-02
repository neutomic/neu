<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres;

use Amp\Sql\Common\CommandResult;
use Amp\Sql\Result;
use Neu\Database\QueryResultInterface;

final class QueryResult implements QueryResultInterface
{
    public function __construct(
        private readonly Result $result,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function nextQueryResult(): ?QueryResultInterface
    {
        $next = $this->result->getNextResult();
        if ($next === null) {
            return null;
        }

        return new self($next);
    }

    /**
     * {@inheritDoc}
     */
    public function getRows(): array
    {
        $rows = [];
        /** @var array<string, mixed> $row */
        foreach ($this->result as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * {@inheritDoc}
     */
    public function getRowCount(): ?int
    {
        if ($this->result instanceof CommandResult) {
            return 0;
        }

        return $this->getAffectedRowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function getAffectedRowCount(): ?int
    {
        /** @var null|int<0, max> */
        return $this->result->getRowCount();
    }
}
