<?php

declare(strict_types=1);

namespace Neu\Console\Formatter;

final class NullFormatter extends AbstractFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(string $message, int $width = 0): string
    {
        return $message;
    }
}
