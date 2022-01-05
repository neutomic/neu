<?php

declare(strict_types=1);

namespace Neu\Console\Input;

use Psl\IO;
use Neu\Console\Exception;

/**
 * The `Input` class contains all available `Flag`, `Argument`, `Option`, and
 * `Command` objects available to parse given the provided input.
 */
interface InputInterface
{
    /**
     * Add a new `Argument` candidate to be parsed from input.
     */
    public function addArgument(Definition\Argument $argument): self;

    /**
     * Add a new `Flag` candidate to be parsed from input.
     */
    public function addFlag(Definition\Flag $flag): self;

    /**
     * Add a new `Option` candidate to be parsed from input.
     */
    public function addOption(Definition\Option $option): self;

    /**
     * Parse and retrieve the active command name from the raw input.
     */
    public function getActiveCommand(): ?string;

    /**
     * Retrieve an `Argument` by its key or alias. Returns null if none exists.
     */
    public function getArgument(string $key): Definition\Argument;

    /**
     * Retrieve all `Argument` candidates.
     */
    public function getArguments(): Bag\ArgumentBag;

    /**
     * Retrieve a `Flag` by its key or alias. Returns null if none exists.
     */
    public function getFlag(string $key): Definition\Flag;

    /**
     * Retrieve all `Flag` candidates.
     */
    public function getFlags(): Bag\FlagBag;

    /**
     * Retrieve an `Option` by its key or alias. Returns null if none exists.
     */
    public function getOption(string $key): Definition\Option;

    /**
     * Retrieve all `Option` candidates.
     */
    public function getOptions(): Bag\OptionBag;

    /**
     * Read in and return input from the user.
     *
     * @throws Exception\NonInteractiveInputException
     */
    public function getUserInput(?int $length = null): string;

    /**
     * Return the underlying `IO\ReadHandleInterface` associated with this `Input` object.
     */
    public function getHandle(): IO\ReadHandleInterface;

    /**
     * Parse input for all `Flag`, `Option`, and `Argument` candidates.
     */
    public function parse(bool $rewind = false): void;

    /**
     * Validate all `Flag`, `Option`, and `Argument` candidates.
     */
    public function validate(): void;

    /**
     * Set the arguments. This will override all existing arguments.
     */
    public function setArguments(Bag\ArgumentBag $arguments): self;

    /**
     * Set the flags. This will override all existing flags.
     */
    public function setFlags(Bag\FlagBag $flags): self;

    /**
     * Set the options. This will override all existing options.
     */
    public function setOptions(Bag\OptionBag $options): self;

    /**
     * Return whether the input is interactive.
     */
    public function isInteractive(): bool;

    /**
     * Sets the input interactivity.
     */
    public function setInteractive(bool $interactive): self;
}
