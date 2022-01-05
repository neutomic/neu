<?php

declare(strict_types=1);

namespace Neu\Console\Input\Definition;

use Neu\Console\Exception;

/**
 * An `DefinitionInterface` defines the name and type of input that may be accepted
 * by the user.
 *
 * @template T
 */
interface DefinitionInterface
{
    /**
     * Returns if the `DefinitionInterface` has been assigned a value by the parser.
     */
    public function exists(): bool;

    /**
     * Retrieve the alias of the `DefinitionInterface`.
     */
    public function getAlias(): string;

    /**
     * Retrieve the description of the `DefinitionInterface`.
     */
    public function getDescription(): string;

    /**
     * Retrieve the formatted name suitable for output in a help screen or
     * documentation.
     */
    public function getFormattedName(string $name): string;

    /**
     * Retrieve the mode of the `DefinitionInterface`.
     */
    public function getMode(): Mode;

    /**
     * Retrieve the name of the `DefinitionInterface`.
     */
    public function getName(): string;

    /**
     * Retrieve the value of the `DefinitionInterface` as specified by the user.
     *
     * @throws Exception\MissingValueException If the definition has not been assigned a value.
     *
     * @return T
     */
    public function getValue(): mixed;
}
