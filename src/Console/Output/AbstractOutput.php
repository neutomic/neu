<?php

declare(strict_types=1);

namespace Neu\Console\Output;

use Neu\Console\Formatter;
use Psl\IO;
use Psl\Str;

use function strip_tags;

abstract class AbstractOutput implements OutputInterface
{
    /**
     * The global verbosity level for the `Output`.
     */
    protected Verbosity $verbosity;

    /**
     * The formatter instance.
     */
    protected Formatter\FormatterInterface $formatter;

    /**
     * The output cursor.
     */
    protected ?Cursor $cursor = null;

    /**
     * Construct a new `Output` object.
     */
    public function __construct(Verbosity $verbosity = Verbosity::Normal, bool $decorated = false, ?Formatter\FormatterInterface $formatter = null)
    {
        $this->verbosity = $verbosity;
        $this->formatter = $formatter ?? new Formatter\Formatter($decorated);

        $this->setDecorated($decorated);
    }

    /**
     * {@inheritDoc}
     */
    final public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->write($message . OutputInterface::END_OF_LINE, $verbosity, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function getCursor(): Cursor
    {
        if ($this->cursor === null) {
            $this->cursor = new Cursor($this);
        }

        return $this->cursor;
    }

    /**
     * {@inheritDoc}
     */
    public function erase(Sequence\Erase $mode = Sequence\Erase::Line): void
    {
        $string = Str\format('%s%s', OutputInterface::CTRL, $mode->value);

        $this->write($string);
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatter(): Formatter\FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatter(Formatter\FormatterInterface $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosity(): Verbosity
    {
        return $this->verbosity;
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity(Verbosity $verbosity): self
    {
        $this->verbosity = $verbosity;

        return $this;
    }

    /**
     * @internal
     */
    protected function writeTo(IO\WriteHandleInterface $handle, string $message, Verbosity $verbosity, Type $type = Type::Normal): void
    {
        if (!$this->shouldOutput($verbosity)) {
            return;
        }

        $handle->writeAll($this->format($message, $type));
    }

    /**
     * Determine how the given verbosity compares to the class's verbosity level.
     */
    protected function shouldOutput(Verbosity $verbosity): bool
    {
        return ($verbosity->value <= $this->verbosity->value);
    }

    /**
     * {@inheritDoc}
     */
    public function format(string $message, Type $type = Type::Normal): string
    {
        return match ($type) {
            Type::Raw => $message,
            Type::Normal => $this->formatter->format($message),
            Type::Plain => strip_tags($message),
        };
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated(): bool
    {
        return $this->getFormatter()->isDecorated();
    }

    /**
     * {@inheritDoc}
     */
    public function setDecorated(bool $decorated): self
    {
        $this->getFormatter()->setDecorated($decorated);

        return $this;
    }
}
