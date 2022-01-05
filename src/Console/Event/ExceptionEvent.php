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
    public ?int $exitCode = null;

    public function __construct(
        public readonly Input\InputInterface   $input,
        public readonly Output\OutputInterface $output,
        public readonly Exception $exception,
        public readonly ?Command\ConfigurationInterface $configuration,
        public readonly ?Command\CommandInterface $command,
    ) {
        parent::__construct($this->input, $this->output, $this->configuration, $this->command);
    }
}
