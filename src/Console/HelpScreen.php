<?php

declare(strict_types=1);

namespace Neu\Console;

use Neu\Console\Input\Bag;
use Psl\Dict;
use Psl\Iter;
use Psl\Math;
use Psl\Ref;
use Psl\Str;
use Psl\Vec;

use function wordwrap;

/**
 * The `HelpScreen` class renders out a usage screen given the available `Flag`,
 * `Option`, and `Argument` objects available as well as available commands that
 * can be executed.
 */
final class HelpScreen
{
    /**
     * The available `Argument` objects accepted.
     */
    protected Bag\ArgumentBag $arguments;

    /**
     * The current `Command` the `HelpScreen` refers to.
     */
    protected ?Command\Command $command = null;

    /**
     * The available `Command` objects available.
     *
     * @var array<string, Command\Command>
     */
    protected array $commands;

    /**
     * The available `Flag` objects accepted.
     */
    protected Bag\FlagBag $flags;

    /**
     * The optional `name` of the application when not outputting a `HelpScreen`
     * for a specific `Command`.
     */
    protected string $name = '';

    /**
     * The available `Option` objects accepted.
     */
    protected Bag\OptionBag $options;

    /**
     * Construct a new instance of the `HelpScreen`.
     */
    public function __construct(private readonly Application $application, private readonly Terminal $terminal, Input\InputInterface $input)
    {
        $this->commands = $application->all();
        $this->arguments = $input->getArguments();
        $this->flags = $input->getFlags();
        $this->options = $input->getOptions();
    }

    /**
     * Build and return the markup for the `HelpScreen`.
     */
    public function render(): string
    {
        $retval = [];
        $heading = $this->renderHeading();
        if ($heading !== '') {
            $retval[] = $heading;
        }

        $retval[] = $this->renderUsage();
        if (!Iter\is_empty($this->arguments->all())) {
            $output = $this->renderSection($this->arguments);
            if ($output) {
                $retval[] = Str\format(
                    '<fg=yellow>%s</>%s%s',
                    'Arguments',
                    Output\OutputInterface::END_OF_LINE,
                    $output,
                );
            }
        }

        if (!Iter\is_empty($this->flags->all())) {
            $output = $this->renderSection($this->flags);
            if ($output) {
                $retval[] = Str\format(
                    '<fg=yellow>%s</>%s%s',
                    'Flags',
                    Output\OutputInterface::END_OF_LINE,
                    $output,
                );
            }
        }

        if (!Iter\is_empty($this->options->all())) {
            $output = $this->renderSection($this->options);
            if ($output) {
                $retval[] = Str\format('<fg=yellow>%s</>%s%s', 'Options', Output\OutputInterface::END_OF_LINE, $output);
            }
        }

        if (($this->command === null) && !Iter\is_empty($this->commands)) {
            $retval[] = $this->renderCommands();
        }

        return Str\join($retval, Output\OutputInterface::END_OF_LINE . Output\OutputInterface::END_OF_LINE) . Output\OutputInterface::END_OF_LINE;
    }

    /**
     * Build and return the markup for the heading of the `HelpScreen`. This is
     * either the name of the application (when not rendering for a specific
     * `Command`) or the name and description of the `Command`.
     */
    protected function renderHeading(): string
    {
        $retval = [];
        if ($this->command !== null) {
            $command = $this->command;
            $description = $command->getDescription();
            if ($description !== '') {
                $retval[] = $command->getName() . ' - ' . $description;
            } else {
                $retval[] = $command->getName();
            }
        } elseif ($this->application->getName() !== '') {
            $banner = $this->application->getBanner();
            if ($banner !== '') {
                $retval[] = $banner;
            }

            $name = Str\format('<fg=green>%s</>', $this->application->getName());
            $version = $this->application->getVersion();
            if ($version !== '') {
                $name .= Str\format(' version <fg=yellow>%s</>', $version);
            }

            $retval[] = $name;
        }

        return Str\join($retval, Output\OutputInterface::END_OF_LINE);
    }

    /**
     * When rendering a for a `Command`, this method builds and returns the usage.
     */
    protected function renderUsage(): string
    {
        $usage = [];
        if ($this->command !== null) {
            $command = $this->command;

            $usage[] = $command->getName();

            foreach ($command->getFlags() as $flag) {
                $flg = $flag->getFormattedName($flag->getName());
                $alias = $flag->getAlias();
                if (!Str\is_empty($alias)) {
                    $flg .= '|' . $flag->getFormattedName($alias);
                }

                if ($flag->getMode() === Input\Definition\Mode::Optional) {
                    $usage[] = '[' . $flg . ']';
                } else {
                    $usage[] = $flg;
                }
            }

            foreach ($command->getOptions() as $option) {
                $opt = $option->getFormattedName($option->getName());
                $alias = $option->getAlias();
                if (!Str\is_empty($alias)) {
                    $opt .= '|' . $option->getFormattedName($alias);
                }

                $opt .= '="..."';
                if ($option->getMode() === Input\Definition\Mode::Optional) {
                    $usage[] = '[' . $opt . ']';
                } else {
                    $usage[] = $opt;
                }
            }

            foreach ($command->getArguments() as $argument) {
                $arg = $argument->getName();
                $alias = $argument->getAlias();
                if (!Str\is_empty($alias)) {
                    $arg .= '|' . $argument->getFormattedName($alias);
                }

                $arg = '<' . $arg . '>';
                if ($argument->getMode() === Input\Definition\Mode::Optional) {
                    $usage[] = '[' . $arg . ']';
                } else {
                    $usage[] = $arg;
                }
            }
        } else {
            $usage[] = 'command';
            $usage[] = '[--flag|-f]';
            $usage[] = '[--option|-o="..."]';
            $usage[] = '[<argument>]';
        }

        return Str\format(
            '<fg=yellow>Usage</>%s  %s',
            Output\OutputInterface::END_OF_LINE,
            Str\join($usage, ' '),
        );
    }

    /**
     * Build and return a specific section of available `Input` objects the user
     * may specify.
     *
     * @template T of Input\Definition\DefinitionInterface
     *
     * @param Input\AbstractBag<T> $arguments
     */
    protected function renderSection(Input\AbstractBag $arguments): string
    {
        $entries = [];
        foreach ($arguments as $argument) {
            $name = $argument->getFormattedName($argument->getName());
            $alias = $argument->getAlias();
            if (!Str\is_empty($alias)) {
                $name = $argument->getFormattedName($alias) . ', ' . $name;
            }

            $entries[$name] = $argument->getDescription();
        }

        /** @var int */
        $maxLength = Math\max(Vec\map(
            Vec\keys($entries),
            static fn(string $key): int => Str\length($key),
        ));

        $descriptionLength = $this->terminal->getWidth() - 6 - $maxLength;
        $output = [];
        foreach ($entries as $name => $description) {
            $formatted = '  ' . Str\pad_right($name, $maxLength);
            $formatted = Str\format('<fg=green>%s</>', $formatted);
            $description = Str\split(
                wordwrap($description, $descriptionLength, '{{NC-BREAK}}'),
                '{{NC-BREAK}}',
            );
            $formatted .= '  ' . Iter\first($description);
            $description = Vec\values(Dict\drop($description, 1));
            $pad = Str\repeat(' ', $maxLength + 6);
            foreach ($description as $desc) {
                $formatted .= Output\OutputInterface::END_OF_LINE . $pad . $desc;
            }

            $output[] = '  ' . $formatted;
        }

        return Str\join($output, Output\OutputInterface::END_OF_LINE);
    }

    /**
     * Build the list of available `Command` objects that can be called and their
     * descriptions.
     */
    protected function renderCommands(): string
    {
        $this->commands = Dict\sort_by_key($this->commands);

        $maxLength = Math\max(
            Vec\map(
                Vec\keys($this->commands),
                static function ($key): int {
                        $indentation = new Ref(0);
                        Vec\map(
                            Str\chunk($key),
                            static function (string $char) use ($indentation): void {
                                $indentation->value += $char === ':' ? 1 : 0;
                            },
                        );

                        $key = Str\repeat('  ', $indentation->value) . $key;

                        return Str\length($key);
                },
            ),
        ) ?? 0;
        $descriptionLength = $this->terminal->getWidth() - 4 - $maxLength;

        $output = [];
        $nestedNames = [];
        foreach ($this->commands as $name => $command) {
            if ($command->isHidden()) {
                continue;
            }

            $components = Str\split($name, ':');
            $nested = Vec\values(Dict\take($components, Iter\count($components) - 1));

            if (Iter\count($nested) > 0) {
                $nest = '';
                foreach ($nested as $piece) {
                    $nest = $nest ? ':' . $piece : $piece;

                    if (!Iter\contains($nestedNames, $nest)) {
                        // If we get here, then we need to list the name, but it isn't
                        // actually a command.
                        $nestedNames[] = $nest;

                        $indentation = new Ref(0);
                        Vec\map(
                            Str\chunk($name),
                            static function (string $char) use ($indentation): void {
                                $indentation->value += $char === ':' ? 1 : 0;
                            },
                        );

                        $output[] = Str\format(
                            '<bold>%s</>',
                            Str\repeat('  ', $indentation->value) .
                            Str\pad_right($nest, $maxLength),
                        );
                    }
                }
            } else {
                $nestedNames[] = $name;
            }

            $indentation = new Ref(0);
            Vec\map(
                Str\chunk($name),
                static function (string $char) use ($indentation): void {
                    $indentation->value += $char === ':' ? 1 : 0;
                },
            );

            $formatted = Str\format(
                '<%s>%s</>',
                $command->isEnabled() ? 'success' : 'error',
                Str\repeat('  ', $indentation->value) .
                Str\pad_right($name, $maxLength - (2 * $indentation->value)),
            );

            $description = Str\split(
                wordwrap(
                    $command->getDescription(),
                    $descriptionLength,
                    '{{NC-BREAK}}',
                ),
                '{{NC-BREAK}}',
            );
            $formatted .= '  ' . Iter\first($description);
            $description = Vec\values(Dict\drop($description, 1));

            $pad = Str\repeat(' ', $maxLength + 4);
            foreach ($description as $desc) {
                $formatted .= Output\OutputInterface::END_OF_LINE . $pad . $desc;
            }

            $output[] = '  ' . $formatted;
        }

        return Str\format(
            '<fg=yellow>Available Commands:</>%s%s',
            Output\OutputInterface::END_OF_LINE,
            Str\join($output, Output\OutputInterface::END_OF_LINE),
        );
    }

    /**
     * Set the `Argument` objects to render information for.
     */
    public function setArguments(Bag\ArgumentBag $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Set the `Command` to render a the help screen for.
     */
    public function setCommand(Command\Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Set the `Command` objects to render information for.
     *
     * @param array<string, Command\Command> $commands
     */
    public function setCommands(
        array $commands,
    ): self {
        $this->commands = $commands;

        return $this;
    }

    /**
     * Set the `Flag` objects to render information for.
     *
     * @param Bag\FlagBag $flags The `Flag` objects available
     */
    public function setFlags(Bag\FlagBag $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Set the `Input` the help screen should read all available parameters and
     * commands from.
     */
    public function setInput(Input\InputInterface $input): self
    {
        $this->arguments = $input->getArguments();
        $this->flags = $input->getFlags();
        $this->options = $input->getOptions();

        return $this;
    }

    /**
     * Set the name of the application.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the `Option` objects to render information for.
     */
    public function setOptions(Bag\OptionBag $options): self
    {
        $this->options = $options;

        return $this;
    }
}
