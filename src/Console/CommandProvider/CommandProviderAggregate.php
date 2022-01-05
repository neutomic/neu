<?php

declare(strict_types=1);

namespace Neu\Console\CommandProvider;

use Neu\Console\Command;
use Neu\Console\Exception;
use Neu\Console\Internal;
use Neu\Console\Output;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

final class CommandProviderAggregate implements CommandProviderInterface
{
    /**
     * @var list<CommandProviderInterface>
     */
    private array $providers = [];

    public function attach(CommandProviderInterface $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): Reference
    {
        foreach ($this->providers as $provider) {
            try {
                return $provider->get($name);
            } catch (Exception\CommandNotFoundException) {
            }
        }

        throw $this->buildException($name);
    }

    /**
     * @inheritDoc
     */
    public function getAllConfigurations(): array
    {
        return Vec\concat(
            [],
            ...Vec\map(
                $this->providers,
                /**
                 * @return list<Command\ConfigurationInterface>
                 */
                static fn(CommandProviderInterface $provider): array => $provider->getAllConfigurations()
            )
        );
    }

    private function buildException(string $name): Exception\CommandNotFoundException
    {
        $configurations = [];
        foreach ($this->getAllConfigurations() as $configuration) {
            $configurations[$configuration->getName()] = $configuration;
            foreach ($configuration->getAliases() as $alias) {
                $configurations[$alias] = $configurations;
            }
        }

        $all_names = Vec\keys($configurations);
        $message = Str\format('Command "%s" is not defined.', $name);
        $alternatives = Internal\Utility::findAlternatives($name, $all_names);
        if (!Iter\is_empty($alternatives)) {
            // remove hidden commands
            $alternatives = Vec\filter(
                $alternatives,
                static fn(string $name): bool => !$configurations[$name]->isHidden(),
            );
            if (1 === Iter\count($alternatives)) {
                $message .= Str\format(
                    '%s%sDid you mean this?%s%s',
                    Output\OutputInterface::END_OF_LINE,
                    Output\OutputInterface::END_OF_LINE,
                    Output\OutputInterface::END_OF_LINE,
                    Output\OutputInterface::END_OF_LINE,
                );
            } else {
                $message .= Str\format(
                    '%s%sDid you mean one of these?%s%s',
                    Output\OutputInterface::END_OF_LINE,
                    Output\OutputInterface::END_OF_LINE,
                    Output\OutputInterface::END_OF_LINE,
                    Output\OutputInterface::END_OF_LINE,
                );
            }

            foreach ($alternatives as $alternative) {
                $message .= Str\format('    - %s%s', $alternative, Output\OutputInterface::END_OF_LINE);
            }
        }

        return new Exception\CommandNotFoundException($message);
    }
}
