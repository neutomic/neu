<?php

declare(strict_types=1);

namespace Neu\Console;

use Exception as RootException;
use Neu\EventDispatcher;
use Psl\Dict;
use Psl\Env;
use Psl\IO;
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
     * The `CommandProvider` instance to use to lookup commands.
     */
    protected CommandProvider\CommandProviderAggregate $provider;

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
        $this->provider = new CommandProvider\CommandProviderAggregate();
        $this->errorHandler = new ErrorHandler\StandardErrorHandler();
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
     * Add a `CommandProvider` to use for command discovery.
     */
    public function addProvider(CommandProvider\CommandProviderInterface $provider): self
    {
        $this->provider->attach($provider);

        return $this;
    }

    public function getProvider(): CommandProvider\CommandProviderInterface
    {
        return $this->provider;
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
        Env\set_var('COLUMNS', (string) Terminal::getWidth());
        Env\set_var('LINES', (string) Terminal::getHeight());
        if ($input === null) {
            $input = new Input\HandleInput(IO\input_handle(), Vec\values(Dict\drop(Env\args(), 1)));
        }

        if ($output === null) {
            $output = new Output\HandleConsoleOutput(IO\output_handle(), IO\error_handle());
        }

        $reference = null;
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
                $reference = $this->provider->get($command_name);
                $exitCode = $this->runCommand($input, $output, $reference);
            }
        } catch (RootException $exception) {
            $exitCode = null;
            if ($this->dispatcher !== null) {
                $dispatcher = $this->dispatcher;
                $event = $dispatcher->dispatch(new Event\ExceptionEvent($input, $output, $exception, $reference->configuration, $reference->command));
                $exitCode = $event->getExitCode();
            }

            if ($exitCode === null || Command\ExitCode::Success !== $exitCode) {
                $exitCode = $this->errorHandler->handle($input, $output, $exception, $reference);
            }
        }

        return $this->terminate($input, $output, $reference, $exitCode instanceof Command\ExitCode ? $exitCode->value : $exitCode);
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
    protected function renderHelpScreen(Input\InputInterface $input, Output\OutputInterface $output, ?CommandProvider\Reference $command = null): void
    {
        $helpScreen = new HelpScreen($this, $input);
        if ($command !== null) {
            $helpScreen->setCommandConfiguration($command->configuration);
        }

        $output->write(
            $helpScreen->render()
        );
    }


    /**
     * Register and run the `Command` object.
     */
    public function runCommand(
        Input\InputInterface $input,
        Output\OutputInterface $output,
        CommandProvider\Reference $reference,
    ): int {
        $command = $reference->command;
        $configuration = $reference->configuration;
        if ($command instanceof Command\ApplicationAwareCommandInterface) {
            $command->setApplication($this);
        }

        $arguments = (new Input\Bag\ArgumentBag())->add($configuration->getArguments()->all());
        foreach ($input->getArguments()->getIterator() as $name => $argument) {
            $arguments->set($name, $argument);
        }
        $input->setArguments($arguments);

        $flags = (new Input\Bag\FlagBag())->add($configuration->getFlags()->all());
        foreach ($input->getFlags()->getIterator() as $name => $flag) {
            $flags->set($name, $flag);
        }
        $input->setFlags($flags);

        $options = (new Input\Bag\OptionBag())->add($configuration->getOptions()->all());
        foreach ($input->getOptions()->getIterator() as $name => $option) {
            $options->set($name, $option);
        }
        $input->setOptions($options);

        $input->parse(true);
        if ($input->getFlag('help')->getValue() === 1) {
            $this->renderHelpScreen($input, $output, $reference);
            return 0;
        }

        if ($input->getFlag('version')->getValue() === 1) {
            $this->renderVersionInformation($output);
            return 0;
        }

        $input->validate();

        $dispatcher = $this->dispatcher;
        if ($dispatcher === null) {
            return $command->run($input, $output);
        }

        // Dispatch the `BeforeExecuteEvent` event to all registered listeners.
        $event = $dispatcher->dispatch(
            new Event\BeforeExecuteEvent($input, $output, $reference->configuration, $reference->command),
        );

        if ($event->commandShouldRun()) {
            $exitCode = $command->run($input, $output);
        } else {
            $exitCode = Command\ExitCode::SkippedCommand->value;
        }

        return $exitCode;
    }

    /**
     * Termination method executed at the end of the application's run.
     */
    protected function terminate(
        Input\InputInterface $input,
        Output\OutputInterface $output,
        ?CommandProvider\Reference $reference,
        int $exitCode
    ): int {
        if ($this->dispatcher !== null) {
            $dispatcher = $this->dispatcher;
            // Dispatch the `AfterExecuteEvent` event to all registered listeners.
            $event = $dispatcher->dispatch(
                new Event\AfterExecuteEvent($input, $output, $reference?->configuration, $reference?->command, $exitCode),
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
