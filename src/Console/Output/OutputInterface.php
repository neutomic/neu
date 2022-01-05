<?php

declare(strict_types=1);

namespace Neu\Console\Output;

use Neu\Console\Formatter;

use const PHP_EOL;

interface OutputInterface
{
    public const TAB = "\t";
    public const END_OF_LINE = PHP_EOL;
    public const CTRL = "\r";

    /**
     * Format contents by parsing the style tags and applying necessary formatting.
     */
    public function format(string $message, Type $type = Type::Normal): string;

    /**
     * Send output to the standard output stream.
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void;

    /**
     * Send output to the standard output stream with a new line character appended to the message.
     */
    public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void;

    /**
     * Get the output cursor.
     */
    public function getCursor(): Cursor;

    /**
     * Clears all characters.
     */
    public function erase(Sequence\Erase $mode = Sequence\Erase::Line): void;

    /**
     * Set the formatter instance.
     */
    public function setFormatter(Formatter\FormatterInterface $formatter): self;

    /**
     * Returns the formatter instance.
     */
    public function getFormatter(): Formatter\FormatterInterface;

    /**
     * Set the global verbosity of the `Output`.
     */
    public function setVerbosity(Verbosity $verbosity): self;

    /**
     * Get the global verbosity of the `Output`.
     */
    public function getVerbosity(): Verbosity;

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
}
