<?php

declare(strict_types=1);

namespace Neu\Console\Style;

use Generator;
use Neu\Console;
use Neu\Console\Feedback;
use Neu\Console\Formatter;
use Neu\Console\Input;
use Neu\Console\Output;
use Neu\Console\Output\Cursor;
use Neu\Console\Output\Sequence;
use Neu\Console\Output\Type;
use Neu\Console\Output\Verbosity;
use Neu\Console\Table;
use Neu\Console\Tree;
use Neu\Console\UserInput;
use Psl\Str;
use Psl\Vec;

final class Style implements StyleInterface
{
    private Output\BufferedOutput $buffer;

    public function __construct(
        private readonly Console\TerminalInterface $terminal,
        private readonly Input\InputInterface $input,
        private readonly Output\OutputInterface $output
    ) {
        $this->buffer = new Output\BufferedOutput($output->getVerbosity(), $output->isDecorated(), $output->getFormatter());
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
    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
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
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->output->write($message, $verbosity, $type);
        $this->buffer->write($message, $verbosity, $type);
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
        $this->buffer->erase($mode);
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatter(Formatter\FormatterInterface $formatter): self
    {
        $this->output->setFormatter($formatter);
        $this->buffer->setFormatter($formatter);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity(Verbosity $verbosity): self
    {
        $this->output->setVerbosity($verbosity);
        $this->buffer->setVerbosity($verbosity);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setDecorated(bool $decorated): self
    {
        $this->output->setDecorated($decorated);
        $this->buffer->setDecorated($decorated);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function title(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->autoPrependBlock($verbosity);

        $this->writeLine(
            Str\format(
                '<comment>%s</>',
                Formatter\Formatter::escapeTrailingBackslash($message),
            ),
            $verbosity,
        );

        $this->writeLine(
            Str\format('<comment>%s</>', Str\repeat(
                '=',
                Str\length($this->output->format($message, Output\Type::Plain)),
            )),
            $verbosity,
        );

        $this->nl(1, $verbosity);
    }

    /**
     * @inheritDoc
     */
    public function nl(int $count = 1, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->output->writeLine('');
            $this->buffer->writeLine('');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->output->writeLine($message, $verbosity, $type);
        $this->buffer->writeLine($message, $verbosity, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function format(string $message, Type $type = Type::Normal): string
    {
        return $this->output->format($message, $type);
    }

    /**
     * @inheritDoc
     */
    public function section(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->autoPrependBlock($verbosity);

        $this->writeLine(
            Str\format('<comment>%s</comment>', Formatter\Formatter::escapeTrailingBackslash($message)),
            $verbosity,
        );

        $this->writeLine(
            Str\format('<comment>%s</comment>', Str\repeat('-', Str\length($this->output->format($message, Output\Type::Plain)))),
            $verbosity,
        );

        $this->nl(1, $verbosity);
    }

    /**
     * @inheritDoc
     */
    public function text(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->autoPrependText($verbosity);
        $this->writeLine(Str\format(' %s', $message), $verbosity);
    }

    /**
     * @inheritDoc
     */
    public function success(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->block($message, $verbosity, 'OK', 'fg=black;bg=green', ' ', true);
    }

    /**
     * @inheritDoc
     */
    public function block(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal, ?string $type = null, ?string $style = null, string $prefix = '', bool $padding = false, bool $escape = true, bool $indent = true): void
    {
        $this->autoPrependBlock($verbosity);
        foreach ($this->createBlock($message, $type, $style, $prefix, $padding, $escape, $indent) as $line) {
            $this->writeLine($line, $verbosity);
        }

        $this->nl(1, $verbosity);
    }

    /**
     * @return Generator<int, string, void, void>
     */
    private function createBlock(
        string  $message,
        ?string $type = null,
        ?string $style = null,
        string  $prefix = ' ',
        bool    $padding = false,
        bool    $escape = false,
        bool    $indent = true,
    ): Generator {
        $width = $this->terminal->getWidth();

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

            yield $line;
        }
    }

    /**
     * @inheritDoc
     */
    public function error(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->block($message, $verbosity, 'ERROR', 'error', ' ', true);
    }

    /**
     * @inheritDoc
     */
    public function warning(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->block($message, $verbosity, 'ERROR', 'warning', ' ', true);
    }

    /**
     * @inheritDoc
     */
    public function note(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->block($message, $verbosity, 'NOTE', 'comment', ' ! ');
    }

    /**
     * @inheritDoc
     */
    public function caution(string $message, Output\Verbosity $verbosity = Output\Verbosity::Normal): void
    {
        $this->block($message, $verbosity, 'CAUTION', 'error', ' ! ', true);
    }

    /**
     * @inheritDoc
     */
    public function tree(array $elements): Tree\TreeInterface
    {
        return new Tree\AsciiTree($elements);
    }

    /**
     * @inheritDoc
     */
    public function table(): Table\TableInterface
    {
        return new Table\AsciiTable();
    }

    /**
     * @inheritDoc
     */
    public function confirm(string $default = ''): UserInput\Confirm
    {
        $confirm = new UserInput\Confirm($this->input, $this);
        $confirm->setDefault($default);
        $confirm->setStrict(true);

        return $confirm;
    }

    /**
     * @inheritDoc
     */
    public function menu(array $choices): UserInput\Menu
    {
        $menu = new UserInput\Menu($this->input, $this);
        $menu->setAcceptedValues($choices);
        $menu->setStrict(true);

        return $menu;
    }

    /**
     * @inheritDoc
     */
    public function progress(int $total, string $message = '', int $interval = 100): Feedback\ProgressBarFeedback
    {
        $progress = new Feedback\ProgressBarFeedback(
            $this->terminal,
            $this,
            $total,
            $message,
            $interval,
        );

        $progress->setCharacterSequence(['▓', '', '░']);

        return $progress;
    }

    /**
     * @inheritDoc
     */
    public function wait(int $total, string $message = '', int $interval = 100): Feedback\WaitFeedback
    {
        return new Feedback\WaitFeedback($this->terminal, $this, $total, $message, $interval);
    }

    private function autoPrependBlock(Output\Verbosity $verbosity): void
    {
        $length = Str\length(Str\repeat(Output\OutputInterface::END_OF_LINE, 2));
        $buffer = $this->buffer->getBuffer();
        if (Str\length($buffer) < $length) {
            $chars = '';
        } else {
            $chars = Str\slice($buffer, -$length);
        }

        if ('' === $chars) {
            $this->nl(1, $verbosity);

            return;
        }

        if (Str\ends_with($chars, Output\OutputInterface::END_OF_LINE)) {
            if (!Str\starts_with($chars, Output\OutputInterface::END_OF_LINE)) {
                $this->nl(1, $verbosity);
            }

            return;
        }

        $this->nl(2, $verbosity);
    }

    /**
     * Prepend a new line if the last outputted content isn't "Output\IOutput::EndOfLine".
     */
    private function autoPrependText(Output\Verbosity $verbosity): void
    {
        $content = $this->buffer->getBuffer();
        if (!Str\ends_with($content, Output\OutputInterface::END_OF_LINE)) {
            $this->nl(1, $verbosity);
        }
    }
}
