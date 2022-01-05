<?php

declare(strict_types=1);

namespace Neu\Console\CommandProvider;

use Neu\Console\Command;
use Neu\Console\Exception;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;
use ReflectionAttribute;
use ReflectionObject;

final class AttributeCommandProvider implements CommandProviderInterface
{
    /**
     * @var array<string, Reference>
     */
    private array $commands = [];

    public function register(Command\CommandInterface $command): self
    {
        $configuration = $this->readConfiguration($command);
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

    private function readConfiguration(Command\CommandInterface $command): Command\Configuration
    {
        $reflection = new ReflectionObject($command);
        $attributes = $reflection->getAttributes(Command\Configuration::class, ReflectionAttribute::IS_INSTANCEOF);
        $attribute = $attributes[0] ?? null;
        if (null === $attribute) {
            throw new Exception\InvalidArgumentException(
                Str\format('Command class "%s" does not have "%s" attribute.', $command::class, Command\Configuration::class)
            );
        }

        return $attribute->newInstance();
    }
}
