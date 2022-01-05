<?php

declare(strict_types=1);

namespace Neu\Console\Block;

use Neu\Console\Output;

interface BlockInterface
{
    /**
     * Display the block with the given messages.
     */
    public function display(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): self;
}
