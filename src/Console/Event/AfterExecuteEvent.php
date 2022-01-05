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
    public function __construct(
        public readonly Input\InputInterface   $input,
        public readonly Output\OutputInterface $output,
        public readonly ?Command\ConfigurationInterface $configuration,
        public readonly ?Command\CommandInterface $command,
        public int $exitCode,
    ) {
        parent::__construct($this->input, $this->output, $this->configuration, $this->command);
    }
}
