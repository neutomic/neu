<?php

declare(strict_types=1);

namespace Neu\Console\Event;

use Neu\Console\Command;
use Neu\Console\Input;
use Neu\Console\Output;

/**
 * Allows inspecting input and output of a command.
 *
 * @internal
 */
abstract class Event
{
    public function __construct(
        protected readonly Input\InputInterface   $input,
        protected readonly Output\OutputInterface $output,
        protected readonly ?Command\Command       $command,
    ) {
    }

    public function getInput(): Input\InputInterface
    {
        return $this->input;
    }

    public function getOutput(): Output\OutputInterface
    {
        return $this->output;
    }

    public function getCommand(): ?Command\Command
    {
        return $this->command;
    }
}
