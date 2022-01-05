<?php

declare(strict_types=1);

namespace Neu\Console\CommandProvider;

use Neu\Console\Command;
use Neu\Console\Exception;

interface CommandProviderInterface
{
    /**
     * Get a reference to a command by its name.
     *
     * @throws Exception\CommandNotFoundException If the command is not found.
     */
    public function get(string $name): Reference;

    /**
     * Return a list of all configurations.
     *
     * @return list<Command\ConfigurationInterface>
     */
    public function getAllConfigurations(): array;
}
