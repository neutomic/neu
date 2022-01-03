<?php

declare(strict_types=1);

namespace Neu\Console\Event;

use Exception;
use Neu\Console\Command;
use Neu\Console\Input;
use Neu\Console\Output;

/**
 * Allows handling exceptions thrown while running a command.
 */
final class ExceptionEvent extends Event
{
    private ?int $exitCode;

    public function __construct(
        Input\InputInterface   $input,
        Output\OutputInterface $output,
        private Exception      $exception,
        ?Command\Command       $command,
    ) {
        parent::__construct($input, $output, $command);
    }

    public function getException(): Exception
    {
        return $this->exception;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }
}
