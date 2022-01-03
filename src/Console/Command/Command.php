<?php

declare(strict_types=1);

namespace Neu\Console\Command;

use Neu\Console;
use Neu\Console\Input;
use Neu\Console\Input\Bag;
use Neu\Console\Input\Definition;
use Neu\Console\Output;

/**
 * A `Command` is a class that configures necessary command line inputs from the
 * user and executes its `run` method when called.
 */
abstract class Command
{
    /**
     * The name of the command passed into the command line.
     */
    protected string $name = '';

    /**
     * The aliases for the command name.
     *
     * @var list<string>
     */
    protected array $aliases = [];

    /**
     * The description of the command used when rendering its help screen.
     */
    protected string $description = '';

    protected bool $hidden = false;

    /**
     * Bag container holding all registered `Argument` objects.
     */
    protected Bag\ArgumentBag $arguments;

    /**
     * Bag container holding all registered `Flag` objects.
     */
    protected Bag\FlagBag $flags;

    /**
     * Bag container holding all registered `Option` objects.
     */
    protected Bag\OptionBag $options;

    /**
     * The `Input` object containing all registered and parsed command line
     * parameters.
     */
    protected Input\InputInterface $input;

    /**
     * The `Output` object to handle output to the user.
     */
    protected Output\OutputInterface $output;

    /**
     * The `Application` that is currently running the command.
     */
    protected Console\Application $application;

    /**
     * The `Terminal` instance.
     */
    protected Console\TerminalInterface $terminal;

    private ?Console\Style\StyleInterface $style = null;

    /**
     * Construct a new instance of a command.
     */
    public function __construct(string $name = '')
    {
        $this->arguments = new Bag\ArgumentBag();
        $this->flags = new Bag\FlagBag();
        $this->options = new Bag\OptionBag();
        if ('' !== $name) {
            $this->name($name);
        }

        $this->configure();
    }

    /**
     * The configure method that sets up name, description, and necessary parameters
     * for the `Command` to run.
     */
    abstract public function configure(): void;

    /**
     * The method that stores the code to be executed when the `Command` is run.
     */
    abstract public function run(): int;

    /**
     * Register input definitions in the input object.
     */
    public function registerInput(): self
    {
        $arguments = (new Bag\ArgumentBag())->add($this->arguments->all());
        foreach (            $this->input->getArguments()->getIterator() as $name => $argument
        ) {
            $arguments->set($name, $argument);
        }
        $this->input->setArguments($arguments);

        $flags = (new Bag\FlagBag())->add($this->flags->all());
        foreach ($this->input->getFlags()->getIterator() as $name => $flag) {
            $flags->set($name, $flag);
        }
        $this->input->setFlags($flags);

        $options = (new Bag\OptionBag())->add($this->options->all());
        foreach ($this->input->getOptions()->getIterator() as $name => $option) {
            $options->set($name, $option);
        }
        $this->input->setOptions($options);

        return $this;
    }

    /**
     * Set the command's name.
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the aliases for the command.
     */
    public function alias(string ...$aliases): self
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * Set the command's description.
     */
    public function describe(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the command `Input` object.
     */
    public function setInput(Input\InputInterface $input): self
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Set the command `Output` object.
     */
    public function setOutput(Output\OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Add a new `Argument` to be registered and parsed with the `Input`.
     */
    public function argument(Definition\Argument $argument): self
    {
        $this->arguments->set($argument->getName(), $argument);

        return $this;
    }

    /**
     * Add a new `Flag` to be registered and parsed with the `Input`.
     */
    public function flag(Definition\Flag $flag): self
    {
        $this->flags->set($flag->getName(), $flag);

        return $this;
    }

    /**
     * Add a new `Option` to be registered and parsed with the `Input`.
     */
    public function option(Definition\Option $option): self
    {
        $this->options->set($option->getName(), $option);

        return $this;
    }

    /**
     * Set whether the command should be hidden from the list of commands.
     */
    public function hide(bool $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * Checks whether the command is enabled or not in the current environment.
     *
     * Override this to check for x or y and return false if the command can not
     * run properly under the current conditions.
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * Checks whether the command should be publicly shown or not.
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Retrieve all `Argument` objects registered specifically to this command.
     */
    public function getArguments(): Bag\ArgumentBag
    {
        return $this->arguments;
    }

    /**
     * Retrieve the command's description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Retrieve all `Flag` objects registered specifically to this command.
     */
    public function getFlags(): Bag\FlagBag
    {
        return $this->flags;
    }

    /**
     * Retrieve the command's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieve all `Option` objects registered specifically to this command.
     */
    public function getOptions(): Bag\OptionBag
    {
        return $this->options;
    }

    /**
     * Returns the aliases for the command.
     *
     * @return list<string>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }


    public function setApplication(Console\Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function setTerminal(Console\Terminal $terminal): self
    {
        $this->terminal = $terminal;

        return $this;
    }

    protected function io(): Console\Style\StyleInterface
    {
        return $this->style ?? ($this->style = new Console\Style\Style($this->terminal, $this->input, $this->output));
    }


    /**
     * Retrieve an `Argument` value by key.
     */
    protected function getArgument(string  $key, ?string $default = null): ?string
    {
        return $this->input->getArgument($key)->getValue($default);
    }

    /**
     * Retrieve a `Flag` value by key.
     */
    protected function getFlag(string $key, ?int $default = null): ?int
    {
        return $this->input->getFlag($key)->getValue($default);
    }

    /**
     * Retrieve an `Option` value by key.
     */
    protected function getOption(string $key, ?string $default = null): ?string
    {
        return $this->input->getOption($key)->getValue($default);
    }
}
