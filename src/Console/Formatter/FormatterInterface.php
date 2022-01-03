<?php

declare(strict_types=1);

namespace Neu\Console\Formatter;

/**
 * Formatter interface for console output.
 */
interface FormatterInterface
{
    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated(): bool;

    /**
     * Set the decorated flag.
     */
    public function setDecorated(bool $decorated): self;

    /**
     * Adds a new style.
     */
    public function addStyle(string $name, Style\StyleInterface $style): self;

    /**
     * Checks if output formatter has style with specified name.
     */
    public function hasStyle(string $name): bool;

    /**
     * Gets style options from style with specified name.
     */
    public function getStyle(string $name): Style\StyleInterface;

    /**
     * Formats a message according to the given styles.
     */
    public function format(string $message): string;
}
