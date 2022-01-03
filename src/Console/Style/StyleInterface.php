<?php

declare(strict_types=1);

namespace Neu\Console\Style;

use Neu\Console\Feedback;
use Neu\Console\Output;
use Neu\Console\Table;
use Neu\Console\Tree;
use Neu\Console\UserInput;

interface StyleInterface extends Output\OutputInterface
{
    /**
     * Formats a message as a block of text.
     */
    public function block(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal, ?string $type = null, ?string $style = null, string $prefix = '', bool $padding = false, bool $escape = true, bool $indent = true): void;

    /**
     * Formats a command title.
     */
    public function title(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Formats a section title.
     */
    public function section(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Formats informational text.
     */
    public function text(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Formats a success result bar.
     */
    public function success(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Formats an error result bar.
     */
    public function error(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Formats a warning result bar.
     */
    public function warning(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Formats a note admonition.
     */
    public function note(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Formats a caution admonition.
     */
    public function caution(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Add a newline(s).
     */
    public function nl(int $count = 1, Output\Verbosity $verbosity = Output\Verbosity::Normal): void;

    /**
     * Construct and return a new `Tree` object.
     *
     * @template Tk of array-key
     * @template Tv
     *
     * @param array<Tk, Tv> $elements
     *
     * @return Tree\TreeInterface<Tk, Tv>
     */
    public function tree(array $elements): Tree\TreeInterface;

    /**
     * Construct and return a new `Table` object.
     */
    public function table(): Table\TableInterface;

    /**
     * Construct and return a new `Confirm` object given the default answer.
     */
    public function confirm(string $default = ''): UserInput\Confirm;

    /**
     * Construct and return a new `Menu` object given the choices and display
     * message.
     *
     * @param array<string, string> $choices
     */
    public function menu(array $choices): UserInput\Menu;

    /**
     * Construct and return a new instance of `ProgressBarFeedback`.
     */
    public function progress(int $total, string $message = '', int $interval = 100): Feedback\ProgressBarFeedback;

    /**
     * Construct and return a new `WaitFeedback` object.
     *
     * @param int $total The total number of cycles of the process
     * @param string $message The message presented with the feedback
     * @param int $interval The time interval the feedback should update
     */
    public function wait(int $total, string $message = '', int $interval = 100): Feedback\WaitFeedback;
}
