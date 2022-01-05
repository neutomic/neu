<?php

declare(strict_types=1);

namespace Neu\Console\ErrorHandler;

use Exception;
use Neu\Console\CommandProvider;
use Neu\Console\Input;
use Neu\Console\Output;

interface ErrorHandlerInterface
{
    /**
     * Handle the given error and return the proper exit code.
     */
    public function handle(Input\InputInterface $input, Output\OutputInterface $output, Exception $exception, ?CommandProvider\Reference $command = null): int;
}
