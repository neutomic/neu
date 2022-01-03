<?php

declare(strict_types=1);

namespace Neu\Console\Event;

use Neu\Console\Command;
use Neu\Console\Input;
use Neu\Console\Output;

/**
 * Allows to manipulate the exit code of a command after its execution.
 */
final class AfterExecuteEvent extends Event
{
    private int $exitCode;

    public function __construct(Input\InputInterface $input, Output\OutputInterface $output, ?Command\Command $command, int $exitCode)
    {
        parent::__construct($input, $output, $command);

        $this->exitCode = $exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }
}
