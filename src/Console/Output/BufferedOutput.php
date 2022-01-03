<?php

declare(strict_types=1);

namespace Neu\Console\Output;

final class BufferedOutput extends AbstractOutput
{
    private string $buffer = '';

    /**
     * {@inheritDoc}
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        if (!$this->shouldOutput($verbosity)) {
            return;
        }

        $this->buffer .= $this->format($message, $type);
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
