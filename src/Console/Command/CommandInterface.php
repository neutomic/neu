<?php

declare(strict_types=1);

namespace Neu\Console\Command;

use Neu\Console\Input;
use Neu\Console\Output;

interface CommandInterface
{
    /**
     * The method that stores the code to be executed when the command is run.
     */
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int;
}
