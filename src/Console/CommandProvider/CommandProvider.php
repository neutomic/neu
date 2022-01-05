<?php

declare(strict_types=1);

namespace Neu\Console\CommandProvider;

use Neu\Console\Command;
use Neu\Console\Exception;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

final class CommandProvider implements CommandProviderInterface
{
    /**
     * @var array<string, Reference>
     */
    private array $commands = [];

    public function register(Command\ConfigurationInterface $configuration, Command\CommandInterface $command): self
    {
        $this->commands[$configuration->getName()] = $reference = new Reference($configuration, $command);
        foreach ($configuration->getAliases() as $alias) {
            $this->commands[$alias] = $reference;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): Reference
    {
        if (Iter\contains_key($this->commands, $name)) {
            return $this->commands[$name];
        }

        throw new Exception\CommandNotFoundException('Command "%s" is not registered in the current provider.');
    }

    /**
     * @inheritDoc
     */
    public function getAllConfigurations(): array
    {
        return Vec\map(
            Dict\unique($this->commands),
            static fn(Reference $reference): Command\ConfigurationInterface => $reference->configuration
        );
    }
}
