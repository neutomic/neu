<?php

declare(strict_types=1);

namespace Neu\Console\Exception;

use Neu\Console\Command;
use RuntimeException;

/**
 * Exception thrown when an invalid command name is provided to the application.
 */
final class CommandNotFoundException extends RuntimeException implements ExceptionInterface
{
    public function getExitCode(): Command\ExitCode
    {
        return Command\ExitCode::CommandNotFound;
    }
}
