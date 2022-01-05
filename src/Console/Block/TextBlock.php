<?php

declare(strict_types=1);

namespace Neu\Console\Block;

use Neu\Console\Output;
use Neu\Console\Terminal;
use Psl\Str;
use Psl\Vec;

final class TextBlock implements BlockInterface
{
    public function __construct(private readonly Output\OutputInterface $output)
    {
    }

    /**
     * @inheritDoc
     */
    public function display(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): self
    {
        $message = Str\wrap($message, (int)((Terminal::getWidth() / 3) * 2), cut: true);
        $message = Str\join(Vec\map(Str\split($message, Output\OutputInterface::END_OF_LINE), static fn(string $chunk) => '  ' . $chunk), Output\OutputInterface::END_OF_LINE);

        $this->output->writeLine('', $verbosity);
        $this->output->writeLine($message, $verbosity);
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
