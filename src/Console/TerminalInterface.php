<?php

declare(strict_types=1);

namespace Neu\Console;

use Psl\IO;

interface TerminalInterface
{
    public const DEFAULT_HEIGHT = 30;
    public const DEFAULT_WIDTH = 120;

    /**
     * Retrieve the standard input handle.
     */
    public function getInputHandle(): IO\ReadStreamHandleInterface;

    /**
     * Retrieve the standard output handle.
     */
    public function getOutputHandle(): IO\WriteStreamHandleInterface;

    /**
     * Retrieve the standard error output handle, null if unavailable.
     */
    public function getErrorHandle(): ?IO\WriteStreamHandleInterface;

    /**
     * Retrieve the height of the current terminal window.
     */
    public function getHeight(): int;

    /**
     * Retrieve the width of the current terminal window.
     */
    public function getWidth(): int;
}
