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
        public readonly Input\InputInterface   $input,
        public readonly Output\OutputInterface $output,
        public readonly ?Command\ConfigurationInterface $configuration,
        public readonly ?Command\CommandInterface $command,
    ) {
    }
}
