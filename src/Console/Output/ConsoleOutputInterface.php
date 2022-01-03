<?php

declare(strict_types=1);

namespace Neu\Console\Output;

interface ConsoleOutputInterface extends OutputInterface
{
    /**
     * Return the standard error output.
     */
    public function getErrorOutput(): OutputInterface;
}
