<?php

declare(strict_types=1);

namespace Neu\Console\Block;

use Neu\Console\Formatter;
use Neu\Console\Output;
use Neu\Console\Terminal;
use Psl\Str;
use Psl\Vec;

class Block implements BlockInterface
{
    public function __construct(
        private readonly Output\OutputInterface $output,
        private ?string $type = null,
        private ?string $style = null,
        private string  $prefix = ' ',
        private bool    $padding = false,
        private bool    $escape = false,
        private bool    $indent = true,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function display(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): self
    {
        $type = $this->type;
        $style = $this->style;
        $prefix = $this->prefix;
        $padding = $this->padding;
        $escape = $this->escape;
        $indent = $this->indent;

        $width = Terminal::getWidth();
        $indentLength = 0;
        $lineIndentation = '';
        $prefixLength = Str\length(
            $this->output->format($prefix, Output\Type::Plain),
        );

        if ($type !== null) {
            $type = Str\format('[%s] ', $type);
            if ($indent) {
                $indentLength = Str\length($type);
                $lineIndentation = Str\repeat(' ', $indentLength);
            }

            $message = $type . $message;
        }

        if ($escape) {
            $message = Formatter\Formatter::escape($message);
        }

        $lines = Str\split(
            Str\wrap(
                $message,
                $width - $prefixLength - $indentLength,
                Output\OutputInterface::END_OF_LINE,
                true,
            ),
            Output\OutputInterface::END_OF_LINE,
        );

        $firstLineIndex = 0;
        if ($padding && $this->output->isDecorated()) {
            $firstLineIndex = 1;
            $lines = Vec\concat([''], $lines);
            $lines[] = '';
        }

        $this->output->writeLine('', $verbosity);
        foreach ($lines as $i => $line) {
            if ($type !== null) {
                $line = $firstLineIndex === $i ? $line : $lineIndentation . $line;
            }

            $line = $prefix . $line;
            $fit = $width -
                Str\length($this->output->format($line, Output\Type::Plain));
            if ($fit > 0) {
                $line .= Str\repeat(' ', $fit);
            }

            if ($style) {
                $line = Str\format('<%s>%s</>', $style, $line);
            }

            $this->output->writeLine($line, $verbosity);
        }
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
