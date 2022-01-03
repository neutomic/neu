<?php

declare(strict_types=1);

namespace Neu\Console\Command\Loader;

use Neu\Console\Command;

interface LoaderInterface
{
    /**
     * Loads a command.
     */
    public function get(string $name): Command\Command;

    /**
     * Checks if a command exists.
     */
    public function has(string $name): bool;

    /**
     * @return list<string> All registered command names
     */
    public function getNames(): array;
}
