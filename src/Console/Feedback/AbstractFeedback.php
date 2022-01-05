<?php

declare(strict_types=1);

namespace Neu\Console\Feedback;

use Neu\Console;
use Psl\Math;
use Psl\Str;
use Psl\Vec;

use function microtime;
use function time;

/**
 * `AbstractFeedback` class handles core functionality for calculating and
 * displaying the progress information.
 */
abstract class AbstractFeedback implements FeedbackInterface
{
    /**
     * Characters used in displaying the feedback in the output.
     *
     * @var list<string>
     */
    protected array $characterSequence = [];

    /**
     * @var null|array{0: int, 1: int}
     */
    protected ?array $position = null;

    /**
     * The current cycle out of the given total.
     */
    protected int $current = 0;

    /**
     * The format the feedback indicator will be displayed as.
     */
    protected string $format = '{:prefix}{:feedback}{:suffix}';

    /**
     * The current iteration of the feedback used to calculate the speed.
     */
    protected int $iteration = 0;

    /**
     * The max length of the characters in the character sequence.
     */
    protected int $maxLength = 1;

    /**
     * The template used to prefix the output.
     */
    protected string $prefix = '{:message}  {:percent}% [';

    /**
     * The current speed of the feedback.
     */
    protected float $speed = 0.0;

    /**
     * The time the feedback started.
     */
    protected int $start = -1;

    /**
     * The template used to suffix the output.
     */
    protected string $suffix = '] {:elapsed} / {:estimated}';

    /**
     * The current tick used to calculate the speed.
     */
    protected int $tick = -1;

    /**
     * The feedback running time.
     */
    protected int $timer = -1;

    /**
     * Create a new instance of the `Feedback`.
     */
    public function __construct(
        /**
         * The `Output` used for displaying the feedback information.
         */
        protected Console\Output\OutputInterface $output,
        /**
         * The total number of cycles expected for the feedback to take until finished.
         */
        protected int                            $total = 0,
        /**
         * The message to be displayed with the feedback.
         */
        protected string                         $message = '',
        /**
         * The interval (in milliseconds) between updates of the indicator.
         */
        protected int                            $interval = 100,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function advance(int $increment = 1): void
    {
        $this->current = Math\minva($this->total, $this->current + $increment);

        if ($this->shouldUpdate()) {
            $this->display();
        }

        if ($this->current === $this->total) {
            $this->display(true);
        }
    }

    /**
     * Determine if the feedback should update its output based on the current
     * time, start time, and set interval.
     */
    protected function shouldUpdate(): bool
    {
        $now = microtime(true) * 1000;

        if ($this->timer < 0) {
            $this->timer = (int)$now;
            $this->start = (int)($this->timer / 1000);

            return true;
        }

        if (($now - $this->timer) > $this->interval) {
            $this->timer = (int)$now;

            return true;
        }

        return false;
    }

    /**
     * Method used to format and output the display of the feedback.
     */
    abstract protected function display(bool $finish = false): void;

    /**
     * {@inheritDoc}
     */
    public function finish(): void
    {
        if ($this->current === $this->total) {
            return;
        }

        $this->current = $this->total;
        $this->display(true);
    }

    /**
     * Set the characters used in the output.
     *
     * @var list<string> $characters
     */
    public function setCharacterSequence(array $characters): self
    {
        $this->characterSequence = $characters;
        $this->setMaxLength();

        return $this;
    }

    /**
     * Set the maximum length of the available character sequence characters.
     */
    protected function setMaxLength(): self
    {
        $this->maxLength = (int)Math\max(Vec\map($this->characterSequence, Str\length(...)));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPosition(?array $position): void
    {
        $this->position = $position;
    }

    /**
     * {@inheritDoc}
     */
    public function setInterval(int $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Build and return all variables that are accepted when building the prefix
     * and suffix for the output.
     *
     * @return array<string, string>
     */
    protected function buildOutputVariables(): array
    {
        $message = $this->message;
        $percent = Str\pad_right(
            (string)Math\floor($this->getPercentageComplete() * 100),
            3,
        );
        $estimated = $this->formatTime((int)$this->estimateTimeRemaining());
        $elapsed = Str\pad_right(
            $this->formatTime($this->getElapsedTime()),
            Str\length($estimated),
        );

        return [
            'message' => $message,
            'percent' => $percent,
            'elapsed' => $elapsed,
            'estimated' => $estimated,
        ];
    }

    /**
     * Retrieve the percentage complete based on the current cycle and the total
     * number of cycles.
     */
    protected function getPercentageComplete(): float
    {
        if ($this->total === 0) {
            return 1.0;
        }

        return (float)($this->current / $this->total);
    }

    /**
     * Format the given time for output.
     */
    protected function formatTime(int $time): string
    {
        return ((string)Math\floor($time / 60)) .
            ':' .
            Str\pad_left(((string)($time % 60)), 2, '0');
    }

    /**
     * Given the speed and currently elapsed time, calculate the estimated time
     * remaining.
     */
    protected function estimateTimeRemaining(): float
    {
        $speed = $this->getSpeed();
        if ($speed === null || 0.0 === $speed || !$this->getElapsedTime()) {
            return 0.0;
        }

        return Math\round($this->total / $speed);
    }

    /**
     * Get the current speed of the feedback.
     */
    protected function getSpeed(): float
    {
        if ($this->start < 0) {
            return 0.0;
        }

        if ($this->tick < 0) {
            $this->tick = $this->start;
        }

        $now = microtime(true);
        $span = $now - $this->tick;

        if ($span > 1) {
            $this->iteration++;
            $this->tick = (int)$now;
            $this->speed = (float)(($this->current / $this->iteration) / $span);
        }

        return $this->speed;
    }

    /**
     * Retrieve the current elapsed time.
     */
    protected function getElapsedTime(): int
    {
        if ($this->start < 0) {
            return 0;
        }

        return (time() - $this->start);
    }

    /**
     * Retrieve the total number of cycles the feedback should take.
     */
    protected function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param array<string, string> $variables
     */
    protected function insert(
        string $format,
        array  $variables,
    ): string {
        foreach ($variables as $key => $value) {
            $format = Str\replace($format, Str\format('{:%s}', $key), $value);
        }

        return $format;
    }
}
