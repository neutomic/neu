<?php

declare(strict_types=1);

namespace Neu\Console\ErrorHandler;

use Exception;
use Neu\Console\Command;
use Neu\Console\Input;
use Neu\Console\Output;

interface ErrorHandlerInterface
{
    /**
     * Handle the given error and return the proper exit code.
     */
    public function handle(Input\InputInterface $input, Output\OutputInterface $output, Exception $exception, ?Command\Command $command = null): int;
}
