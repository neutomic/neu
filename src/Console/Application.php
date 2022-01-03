<?php

declare(strict_types=1);

namespace Neu\Console;

use Exception as RootException;
use Neu\EventDispatcher;
use Psl\Dict;
use Psl\Env;
use Psl\Iter;
use Psl\Regex;
use Psl\Str;
use Psl\Vec;

/**
 * The `Application` class bootstraps and handles Input and Output to process and
 * run necessary commands.
 */
class Application
{
    /**
     * A decorator banner to `brand` the application.
     */
    protected string $banner = '';

    /**
     * The `Terminal` instance.
     */
    protected TerminalInterface $terminal;

    /**
     * Store added commands until we inject them into the `Input` at runtime.
     *
     * @var array<string, Command\Command>
     */
    protected array $commands = [];

    /**
     * The `Loader` instances to use to lookup commands.
     *
     * @var list<Command\Loader\LoaderInterface>
     */
    protected array $loaders = [];

    /**
     * Error Handler used to handle exceptions thrown during command execution.
     */
    protected ErrorHandler\ErrorHandlerInterface $errorHandler;

    /**
     * Event Dispatcher to dispatch events during the application lifecycle.
     */
    protected ?EventDispatcher\EventDispatcherInterface $dispatcher = null;

    protected bool $autoExit = true;

    public function __construct(
        /**
         * The name of the application.
         */
        protected string            $name = '',
        /**
         * The version of the application.
         */
        protected string            $version = '',
    ) {
        $this->terminal = new Terminal();
        $this->errorHandler = new ErrorHandler\StandardErrorHandler($this->terminal);
    }

    /**
     * Sets the `EventDispatcher` to be used for dispatching events.
     */
    final public function setEventDispatcher(EventDispatcher\EventDispatcherInterface $dispatcher): self
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Retrieve the `EventDispatcher` instance attached to the application ( if any ).
     */
    final public function getEventDispatcher(): ?EventDispatcher\EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * Retrieve the `ErrorHandler` instance attached to the application.
     */
    final public function getErrorHandler(): ErrorHandler\ErrorHandlerInterface
    {
        return $this->errorHandler;
    }

    /**
     * Sets the `ErrorHandler` to be used for handling exceptions throw during
     * the command execution.
     */
    final public function setErrorHandler(ErrorHandler\ErrorHandlerInterface $errorHandler): self
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }

    /**
     * Add a `CommandLoader` to use for command discovery.
     */
    public function addLoader(Command\Loader\LoaderInterface $loader): self
    {
        foreach ($loader->getNames() as $name) {
            if (!Regex\matches($name, "/^[^\:]++(\:[^\:]++)*$/")) {
                throw new Exception\InvalidCharacterSequenceException(
                    Str\format('Command name "%s" is invalid.', $name),
                );
            }
        }

        $this->loaders[] = $loader;

        return $this;
    }

    /**
     * Add a `Command` to the application to be parsed by the `Input`.
     */
    public function add(Command\Command $command): self
    {
        if (!$command->isEnabled()) {
            return $this;
        }

        $name = $command->getName();
        $aliases = $command->getAliases();
        foreach ([$name, ...$aliases] as $command_name) {
            if (!Regex\matches($command_name, "/^[^\:]++(\:[^\:]++)*$/")) {
                throw new Exception\InvalidCharacterSequenceException(
                    Str\format('Command name "%s" is invalid.', $command_name),
                );
            }
        }

        $this->commands[$name] = $command;

        return $this;
    }

    /**
     * Gets the commands.
     *
     * The container keys are the full names and the values the command instances.
     *
     * @return array<string, Command\Command>
     */
    public function all(): array
    {
        $commands = $this->commands;
        foreach ($this->loaders as $loader) {
            foreach ($loader->getNames() as $name) {
                if (!Iter\contains_key($commands, $name) && $this->has($name)) {
                    $commands[$name] = $this->get($name);
                }
            }
        }

        return $commands;
    }

    /**
     * Returns true if the command exists, false otherwise.
     */
    public function has(string $name): bool
    {
        if (Iter\contains_key($this->commands, $name)) {
            return true;
        }

        foreach ($this->loaders as $loader) {
            if ($loader->has($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a registered command by name or alias.
     */
    public function get(string $name): Command\Command
    {
        if (Iter\contains_key($this->commands, $name)) {
            return $this->commands[$name];
        }

        foreach ($this->loaders as $loader) {
            if ($loader->has($name)) {
                return $loader->get($name);
            }
        }

        throw new Exception\CommandNotFoundException(
            Str\format('The command "%s" does not exist.', $name),
        );
    }

    /**
     * Gets whether to automatically exit after a command execution or not.
     *
     * @return bool Whether to automatically exit after a command execution or not
     */
    public function isAutoExitEnabled(): bool
    {
        return $this->autoExit;
    }

    /**
     * Sets whether to automatically exit after a command execution or not.
     */
    public function setAutoExit(bool $boolean): self
    {
        $this->autoExit = $boolean;

        return $this;
    }

    /**
     * Retrieve the application's banner.
     */
    public function getBanner(): string
    {
        return $this->banner;
    }

    /**
     * Set the banner of the application.
     */
    public function setBanner(string $banner): self
    {
        $this->banner = $banner;

        return $this;
    }

    /**
     * Run the application.
     */
    public function run(?Input\InputInterface $input = null, ?Output\OutputInterface $output = null): int
    {
        Env\set_var('COLUMNS', (string)$this->terminal->getWidth());
        Env\set_var('LINES', (string)$this->terminal->getHeight());
        if ($input === null) {
            $input = new Input\StreamHandleInput($this->terminal->getInputHandle(), Vec\values(Dict\drop(Env\args(), 1)));
        }

        if ($output === null) {
            $output = new Output\StreamHandleConsoleOutput($this->terminal->getOutputHandle(), $this->terminal->getErrorHandle());
        }

        $command = null;
        try {
            $this->bootstrap($input, $output);

            $input->parse();

            $command_name = $input->getActiveCommand();
            if ($input->getFlag('ansi')->getValue() === 1) {
                $output->getFormatter()->setDecorated(true);
            } elseif ($input->getFlag('no-ansi')->getValue() === 1) {
                $output->getFormatter()->setDecorated(false);
            }


            $verbositySet = false;
            if ($input->getFlag('quiet')->getValue() === 1) {
                $verbositySet = true;
                Env\set_var('SHELL_VERBOSITY', (string) Output\Verbosity::Quite->value);

                $output->setVerbosity(Output\Verbosity::Quite);
                $input->setInteractive(false);
            } elseif ($input->getFlag('no-interaction')->getValue() === 1) {
                $input->setInteractive(false);
            }

            if (!$verbositySet) {
                $verbosity = $input->getFlag('verbose')->getValue(0);
                $verbosity = match ($verbosity) {
                    0 => Output\Verbosity::Normal,
                    1 => Output\Verbosity::Verbose,
                    2 => Output\Verbosity::VeryVerbose,
                    default => Output\Verbosity::Debug,
                };

                Env\set_var('SHELL_VERBOSITY', (string) $verbosity->value);
                $output->setVerbosity($verbosity);
            }

            if ($command_name === null) {
                if ($input->getFlag('version')->getValue() === 1) {
                    $this->renderVersionInformation($output);
                } else {
                    $this->renderHelpScreen($input, $output);
                }

                $exitCode = Command\ExitCode::Success;
            } else {
                $command = $this->find($command_name);
                $exitCode = $this->runCommand($input, $output, $command);
            }
        } catch (RootException $exception) {
            $exitCode = null;
            if ($this->dispatcher !== null) {
                $dispatcher = $this->dispatcher;
                $event = $dispatcher->dispatch(new Event\ExceptionEvent($input, $output, $exception, $command));
                $exitCode = $event->getExitCode();
            }

            if ($exitCode === null || Command\ExitCode::Success !== $exitCode) {
                $exitCode = $this->errorHandler->handle($input, $output, $exception, $command);
            }
        }

        return $this->terminate($input, $output, $command, $exitCode instanceof Command\ExitCode ? $exitCode->value : $exitCode);
    }

    /**
     * Bootstrap the `Application` instance with default parameters and global
     * settings.
     */
    protected function bootstrap(Input\InputInterface $input, Output\OutputInterface $output): void
    {
        /*
         * Add global flags
         */
        $input->addFlag(
            (new Input\Definition\Flag('help', 'Display this help screen.'))->alias('h'),
        );

        $input->addFlag(
            (new Input\Definition\Flag('quiet', 'Suppress all output.'))->alias('q'),
        );

        $input->addFlag(
            (new Input\Definition\Flag('verbose', 'Set the verbosity of the application\'s output.'))->alias('v')->setStackable(true),
        );

        $input->addFlag(
            (new Input\Definition\Flag('version', 'Display the application\'s version'))->alias('V'),
        );

        $input->addFlag(
            (new Input\Definition\Flag('no-interaction', 'Force disable input interaction'))->alias('n')
        );

        $input->addFlag(
            (new Input\Definition\Flag('ansi', 'Force enable ANSI output'))
        );

        $input->addFlag(
            (new Input\Definition\Flag('no-ansi', 'Force disable ANSI output'))
        );
    }

    /**
     * Output version information of the current `Application`.
     */
    protected function renderVersionInformation(Output\OutputInterface $output): void
    {
        $name = Str\format('<fg=green>%s</>', $this->getName());
        $version = $this->getVersion();
        if ($version !== '') {
            $name .= Str\format(' version <fg=yellow>%s</>', $version);
        }

        $output->writeLine($name);
    }

    /**
     * Retrieve the application's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the application's version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Render the help screen for the application or the `Command` passed in.
     */
    protected function renderHelpScreen(Input\InputInterface $input, Output\OutputInterface $output, ?Command\Command $command = null): void
    {
        $helpScreen = new HelpScreen($this, $this->terminal, $input);
        if ($command !== null) {
            $helpScreen->setCommand($command);
        }

        $output->write(
            $helpScreen->render()
        );
    }

    /**
     * Finds a command by name or alias.
     *
     * Contrary to get, this command tries to find the best
     * match if you give it an abbreviation of a name or alias.
     */
    public function find(string $name): Command\Command
    {
        foreach ($this->commands as $command) {
            foreach ($command->getAliases() as $alias) {
                if (!$this->has($alias)) {
                    $this->commands[$alias] = $command;
                }
            }
        }

        if ($this->has($name)) {
            return $this->get($name);
        }

        $allCommands = Vec\keys($this->commands);
        foreach ($this->loaders as $loader) {
            $allCommands = Vec\concat($allCommands, $loader->getNames());
        }

        $message = Str\format('Command "%s" is not defined.', $name);
        $alternatives = $this->findAlternatives($name, $allCommands);
        if (!Iter\is_empty($alternatives)) {
            // remove hidden commands
            $alternatives = Vec\filter(
                $alternatives,
                fn(string $name): bool => !$this->get($name)->isHidden(),
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
                $message .= Str\format(
                    '    - %s%s',
                    $alternative,
                    Output\OutputInterface::END_OF_LINE,
                );
            }
        }

        throw new Exception\CommandNotFoundException($message);
    }

    /**
     * Finds alternative of $name among $collection.
     *
     * @param list<string> $collection
     *
     * @return list<string>
     */
    private function findAlternatives(string $name, array $collection): array
    {
        $threshold = 1e3;
        $alternatives = [];
        $collectionParts = [];
        foreach ($collection as $item) {
            $collectionParts[$item] = Str\split($item, ':');
        }

        foreach (Str\split($name, ':') as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = Iter\contains_key($alternatives, $collectionName);
                if (!Iter\contains_key($parts, $i)) {
                    if ($exists) {
                        $alternatives[$collectionName] += $threshold;
                    }

                    continue;
                }

                $lev = (float)Str\levenshtein($subname, $parts[$i]);
                if ($lev <= Str\length($subname) / 3 || ('' !== $subname && Str\contains($parts[$i], $subname))) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }

        foreach ($collection as $item) {
            $lev = (float)Str\levenshtein($name, $item);
            if ($lev <= Str\length($name) / 3 || Str\contains($item, $name)) {
                $alternatives[$item] = Iter\contains_key($alternatives, $item) ? $alternatives[$item] - $lev : $lev;
            }
        }

        return Vec\keys(Dict\sort(Dict\filter($alternatives, static fn($lev) => $lev < (2 * $threshold))));
    }

    /**
     * Register and run the `Command` object.
     */
    public function runCommand(Input\InputInterface $input, Output\OutputInterface $output, Command\Command $command): int
    {
        $command->setApplication($this);
        $command->setTerminal($this->terminal);
        $command->setInput($input);
        $command->setOutput($output);

        $command->registerInput();

        $input->parse(true);
        if ($input->getFlag('help')->getValue() === 1) {
            $this->renderHelpScreen($input, $output, $command);
            return 0;
        }

        if ($input->getFlag('version')->getValue() === 1) {
            $this->renderVersionInformation($output);
            return 0;
        }

        $input->validate();

        $dispatcher = $this->dispatcher;
        if ($dispatcher === null) {
            return $command->run();
        }

        // Dispatch the `BeforeExecuteEvent` event to all registered listeners.
        $event = $dispatcher->dispatch(
            new Event\BeforeExecuteEvent($input, $output, $command),
        );

        if ($event->commandShouldRun()) {
            $exitCode = $command->run();
        } else {
            $exitCode = Command\ExitCode::SkippedCommand->value;
        }

        return $exitCode;
    }

    /**
     * Termination method executed at the end of the application's run.
     */
    protected function terminate(Input\InputInterface $input, Output\OutputInterface $output, ?Command\Command $command, int $exitCode): int
    {
        if ($this->dispatcher !== null) {
            $dispatcher = $this->dispatcher;
            // Dispatch the `AfterExecuteEvent` event to all registered listeners.
            $event = $dispatcher->dispatch(
                new Event\AfterExecuteEvent($input, $output, $command, $exitCode),
            );

            $exitCode = $event->getExitCode();
        }

        if ($exitCode > Command\ExitCode::ExitStatusOutOfRange->value) {
            $exitCode %= Command\ExitCode::ExitStatusOutOfRange->value;
        }

        if ($this->autoExit) {
            exit($exitCode);
        }

        return $exitCode;
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
     * Set the version of the application.
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }
}
