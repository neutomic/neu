<?php

declare(strict_types=1);

namespace Neu\Console\Command;

use Neu\Console\Input\Bag;

interface ConfigurationInterface
{
    /**
     * Retrieve the command's name.
     */
    public function getName(): string;

    /**
     * Retrieve the command's description.
     */
    public function getDescription(): string;

    /**
     * Returns the aliases for the command.
     *
     * @return list<string>
     */
    public function getAliases(): array;

    /**
     * Retrieve all `Flag` objects registered specifically to this command.
     */
    public function getFlags(): Bag\FlagBag;

    /**
     * Retrieve all `Option` objects registered specifically to this command.
     */
    public function getOptions(): Bag\OptionBag;

    /**
     * Retrieve all `Argument` objects registered specifically to this command.
     */
    public function getArguments(): Bag\ArgumentBag;

    /**
     * Checks whether the command should be publicly shown or not.
     */
    public function isHidden(): bool;
}
