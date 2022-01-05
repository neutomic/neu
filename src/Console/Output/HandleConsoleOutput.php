<?php

declare(strict_types=1);

namespace Neu\Console\Output;

use Neu\Console\Formatter;
use Psl\IO;

final class HandleConsoleOutput implements ConsoleOutputInterface
{
    /**
     * Standard output.
     */
    private OutputInterface $output;

    /**
     * Standard error output.
     */
    private OutputInterface $error;

    /**
     * Construct a new `Output` object.
     */
    public function __construct(IO\WriteHandleInterface $output, IO\WriteHandleInterface $error, Verbosity $verbosity = Verbosity::Normal, ?bool $decorated = null, ?Formatter\FormatterInterface $formatter = null)
    {
        $this->output = new HandleOutput($output, $verbosity, $decorated, $formatter);
        $is_decorated = $this->isDecorated();
        $this->error = new HandleOutput($error, $verbosity, $decorated, $formatter);

        if (null === $decorated) {
            $this->setDecorated($is_decorated && $this->error->isDecorated());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function format(string $message, Type $type = Type::Normal): string
    {
        return $this->output->format($message, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->output->write($message, $verbosity, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->output->writeLine($message, $verbosity, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function getCursor(): Cursor
    {
        return $this->output->getCursor();
    }

    /**
     * {@inheritDoc}
     */
    public function erase(Sequence\Erase $mode = Sequence\Erase::Line): void
    {
        $this->output->erase($mode);
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatter(Formatter\FormatterInterface $formatter): self
    {
        $this->output->setFormatter($formatter);
        $this->error->setFormatter($formatter);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatter(): Formatter\FormatterInterface
    {
        return $this->output->getFormatter();
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity(Verbosity $verbosity): self
    {
        $this->output->setVerbosity($verbosity);
        $this->error->setVerbosity($verbosity);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosity(): Verbosity
    {
        return $this->output->getVerbosity();
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorOutput(): OutputInterface
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    /**
     * {@inheritDoc}
     */
    public function setDecorated(bool $decorated): self
    {
        $this->output->setDecorated($decorated);
        $this->error->setDecorated($decorated);

        return $this;
    }
}
