<?php declare(strict_types=1);

namespace Neu\Console\Input;

use Neu\Console;
use Psl\Env;
use Psl\IO;
use Psl\Regex;
use Psl\Str;
use Psl\Vec;
use function function_exists;
use function posix_isatty;
use function sapi_windows_vt100_support;

final class StreamHandleInput implements InputInterface
{
    /**
     * Bag container holding all registered `Argument` objects.
     */
    private Bag\ArgumentBag $arguments;

    /**
     * Bag container holding all registered `Option` objects.
     */
    private Bag\OptionBag $options;

    /**
     * Bag container holding all registered `Flag` objects.
     */
    private Bag\FlagBag $flags;

    /**
     * The `Lexer` that will traverse and help parse the provided input.
     */
    private Lexer $input;

    /**
     * Boolean if the provided input has already been parsed or not.
     */
    private bool $parsed = false;

    /**
     * The active command name (if any) that is parsed from the provided input.
     */
    private ?string $command = null;

    /**
     * All parameters provided in the input that do not match a given `Command`
     * or `Definition`.
     *
     * @var list<array{raw: string, value: string}>
     */
    private array $invalid = [];

    /**
     * Buffered reader for user input.
     */
    private IO\Reader $reader;

    private bool $interactive;

    /**
     * Construct a new instance of Input.
     *
     * @param list<string> $args
     */
    public function __construct(private readonly IO\ReadStreamHandleInterface $handle, array $args)
    {
        $args = Vec\filter($args, static fn(string $arg): bool => '' !== $arg);

        $this->reader = new IO\Reader($handle);
        $this->input = new Lexer($args);
        $this->flags = new Bag\FlagBag();
        $this->options = new Bag\OptionBag();
        $this->arguments = new Bag\ArgumentBag();
        $this->interactive = $this->hasInteractiveStream();
    }

    /**
     * {@inheritDoc}
     */
    public function addArgument(Definition\Argument $argument): self
    {
        $this->arguments->set($argument->getName(), $argument);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addFlag(Definition\Flag $flag): self
    {
        $this->flags->set($flag->getName(), $flag);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addOption(Definition\Option $option): self
    {
        $this->options->set($option->getName(), $option);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveCommand(): ?string
    {
        if ($this->parsed === true) {
            return $this->command;
        }

        if ($this->command !== null) {
            return $this->command;
        }

        $this->parse();

        return $this->command;
    }

    /**
     * {@inheritDoc}
     */
    public function parse(bool $rewind = false): void
    {
        $lexer = $this->input;
        if ($rewind) {
            $lexer = new Lexer(Vec\map($this->invalid, static fn($entry) => $entry['raw']));
        }

        foreach ($lexer as $val) {
            if ($this->parseFlag($val)) {
                continue;
            }

            if ($this->parseOption($val, $lexer)) {
                continue;
            }

            if ($this->command === null && !Lexer::isAnnotated($val['raw'])) {
                // If we haven't parsed a command yet, do so.
                $this->command = $val['value'];
                continue;
            }

            if ($this->parseArgument($val)) {
                continue;
            }

            $this->invalid[] = $val;
        }

        $this->parsed = true;
    }

    /**
     * Determine if a RawInput matches a `Flag` candidate. If so, save its
     * value.
     *
     * @param array{raw: string, value: string} $input
     */
    private function parseFlag(array $input): bool
    {
        $key = $input['value'];
        $flag = $this->flags->get($key);
        if ($flag !== null) {
            if ($flag->isStackable()) {
                $flag->increaseValue();
            } else {
                $flag->assign(1);
            }

            $this->invalid = Vec\filter(
                $this->invalid,
                static fn($entry) => $entry['value'] !== $input['value'],
            );

            return true;
        }

        foreach ($this->flags->getIterator() as $flag) {
            if ($key === $flag->getNegativeAlias()) {
                $flag->assign(0);

                $this->invalid = Vec\filter(
                    $this->invalid,
                    static fn($entry) => $entry['value'] !== $input['value'],
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a RawInput matches an `Option` candidate. If so, save its
     * value.
     *
     * @param array{raw: string, value: string} $input
     */
    protected function parseOption(array $input, Lexer $lexer): bool
    {
        $key = $input['value'];
        $option = $this->options->get($key);
        if ($option === null) {
            return false;
        }

        // Peak ahead to make sure we get a value.
        $nextValue = $lexer->peek();
        if ($nextValue === null) {
            throw new Console\Exception\MissingValueException(
                Str\format('No value given for the option `%s`.', $input['value']),
            );
        }

        if (!$lexer->end() && Lexer::isAnnotated($nextValue['raw'])) {
            throw new Console\Exception\MissingValueException(
                Str\format('No value is present for option `%s`.', $key),
            );
        }

        $lexer->shift();
        $value = $lexer->current();

        if ($matches = Regex\first_match($value['raw'], "#\A\"(.+)\"$#")) {
            $value = $matches[1];
        } else if ($matches = Regex\first_match($value['raw'], "#\A'(.+)'$#")) {
            $value = $matches[1];
        } else {
            $value = $value['raw'];
        }

        $option->assign($value);

        $this->invalid = Vec\filter(
            $this->invalid,
            static fn($entry) => $entry['value'] !== $input['value'],
        );

        return true;
    }

    /**
     * Determine if a RawInput matches an `Argument` candidate. If so, save its
     * value.
     *
     * @param array{raw: string, value: string} $input
     */
    private function parseArgument(array $input): bool
    {
        foreach ($this->arguments as $argument) {
            if ($argument->getValue() === null) {
                $argument->assign($input['raw']);

                $this->invalid = Vec\filter(
                    $this->invalid,
                    static fn($entry) => $entry['value'] !== $input['value'],
                );

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getArgument(string $key): Definition\Argument
    {
        $argument = $this->arguments->get($key);
        if ($argument === null) {
            throw new Console\Exception\InvalidInputDefinitionException(
                Str\format('The argument "%s" does not exist.', $key),
            );
        }

        return $argument;
    }

    /**
     * {@inheritDoc}
     */
    public function getArguments(): Bag\ArgumentBag
    {
        return $this->arguments;
    }

    /**
     * {@inheritDoc}
     */
    public function setArguments(Bag\ArgumentBag $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFlag(string $key): Definition\Flag
    {
        $flag = $this->flags->get($key);
        if ($flag === null) {
            throw new Console\Exception\InvalidInputDefinitionException(
                Str\format('The flag "%s" does not exist.', $key),
            );
        }

        return $flag;
    }

    /**
     * {@inheritDoc}
     */
    public function getFlags(): Bag\FlagBag
    {
        return $this->flags;
    }

    /**
     * {@inheritDoc}
     */
    public function setFlags(Bag\FlagBag $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(string $key): Definition\Option
    {
        $option = $this->options->get($key);
        if ($option === null) {
            throw new Console\Exception\InvalidInputDefinitionException(
                Str\format('The option "%s" does not exist.', $key),
            );
        }

        return $option;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): Bag\OptionBag
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(Bag\OptionBag $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInput(?int $length = null): string
    {
        if ($length !== null) {
            return $this->reader->readFixedSize($length);
        }

        return Str\trim($this->reader->readLine());
    }

    public function getHandle(): IO\ReadHandleInterface
    {
        return $this->reader;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): void
    {
        foreach ($this->flags->getIterator() as $name => $flag) {
            if ($flag->getMode() !== Definition\Mode::Required) {
                continue;
            }

            if ($flag->getValue() === null) {
                throw new Console\Exception\MissingValueException(
                    Str\format('Required flag `%s` is not present.', $name),
                );
            }
        }

        foreach ($this->options->getIterator() as $name => $option) {
            if ($option->getMode() !== Definition\Mode::Required) {
                continue;
            }

            if ($option->getValue() === null) {
                throw new Console\Exception\MissingValueException(
                    Str\format('No value present for required option `%s`.', $name),
                );
            }
        }

        foreach ($this->arguments->getIterator() as $name => $argument) {
            if ($argument->getMode() !== Definition\Mode::Required) {
                continue;
            }

            if ($argument->getValue() === null) {
                throw new Console\Exception\MissingValueException(
                    Str\format('No value present for required argument `%s`.', $name),
                );
            }
        }

        foreach ($this->invalid as $value) {
            throw new Console\Exception\RuntimeException(
                Str\format(
                    'The %s `%s` does not exist.',
                    Lexer::isAnnotated($value['raw']) ? 'option' : 'argument',
                    $value['raw'],
                ),
            );
        }
    }

    public function isInteractive(): bool
    {
        return $this->interactive;
    }

    public function setInteractive(bool $interactive): self
    {
        $this->interactive = $interactive;

        return $this;
    }

    private function hasInteractiveStream(): bool
    {
        $noninteractive = Env\get_var('NONINTERACTIVE');
        if ($noninteractive !== null) {
            if (
                $noninteractive === '1' ||
                $noninteractive === 'true' ||
                $noninteractive === 'yes'
            ) {
                return false;
            }

            if (
                $noninteractive === '0' ||
                $noninteractive === 'false' ||
                $noninteractive === 'no'
            ) {
                return true;
            }
        }

        // Detects TravisCI and CircleCI; Travis gives you a TTY for STDIN
        $ci = Env\get_var('CI');
        if ($ci === '1' || $ci === 'true') {
            return false;
        }

        // Generic
        if (function_exists('posix_isatty') && @posix_isatty($this->handle->getStream())) {
            return true;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support($this->handle->getStream());
        }

        return false;
    }

}
