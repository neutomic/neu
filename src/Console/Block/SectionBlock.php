<?php

declare(strict_types=1);

namespace Neu\Console\Block;

use Neu\Console\Formatter;
use Neu\Console\Output;
use Psl\Str;

final class SectionBlock implements BlockInterface
{
    public function __construct(private readonly Output\OutputInterface $output)
    {
    }

    /**
     * @inheritDoc
     */
    public function display(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): self
    {
        $this->output->writeLine('', $verbosity);
        $this->output->writeLine(Str\format('<comment>%s</comment>', Formatter\Formatter::escapeTrailingBackslash($message)), $verbosity);
        $this->output->writeLine(Str\format('<comment>%s</comment>', Str\repeat('-', Str\length($this->output->format($message, Output\Type::Plain)))), $verbosity);
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
