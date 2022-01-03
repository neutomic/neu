<?php

declare(strict_types=1);

namespace Neu\Console\Output;

use Neu\Console\Formatter;

final class NullOutput extends AbstractOutput implements ConsoleOutputInterface
{
    public function __construct()
    {
        parent::__construct(Verbosity::Quite, new Formatter\NullFormatter());
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatter(Formatter\FormatterInterface $formatter): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity(Verbosity $verbosity): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosity(): Verbosity
    {
        return Verbosity::Quite;
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
    }

    /**
     * Return the standard error output.
     */
    public function getErrorOutput(): OutputInterface
    {
        return new NullOutput();
    }
}
