<?php

declare(strict_types=1);

namespace Neu\Console\Feedback;

use Neu\Console\Terminal;
use Psl\Iter;
use Psl\Str;

/**
 * The `WaitFeedback` class displays feedback by cycling through a series of characters.
 */
final class WaitFeedback extends AbstractFeedback
{
    /**
     * {@inheritDoc}
     */
    protected array $characterSequence = [
        '-',
        '\\',
        '|',
        '/',
    ];

    /**
     * {@inheritDoc}
     */
    protected string $prefix = '{:message} ';

    /**
     * {@inheritDoc}
     */
    protected string $suffix = '';

    /**
     * {@inheritDoc}
     */
    protected function display(bool $finish = false): void
    {
        $variables = $this->buildOutputVariables();

        $index = $this->iteration % Iter\count($this->characterSequence);
        $feedback = Str\pad_right(
            $this->characterSequence[$index],
            $this->maxLength + 1,
        );

        $prefix = $this->insert($this->prefix, $variables);
        $suffix = $this->insert($this->suffix, $variables);
        if (!$this->output->isDecorated()) {
            return;
        }

        $variables = [
            'prefix' => $prefix,
            'feedback' => $feedback,
            'suffix' => $suffix,
        ];

        $width = Terminal::getWidth();
        // format message
        // pad the output to the terminal width
        $output = Str\pad_right($this->insert($this->format, $variables), $width);

        $cursor = null;
        if ($this->position !== null) {
            [$column, $row] = $this->position;
            $cursor = $this->output->getCursor();
            $cursor->save();
            $cursor->move($column, $row);
        }

        $this->output->erase();

        if ($finish) {
            $this->output->writeLine($output);
        } else {
            $this->output->write($output);
        }

        $cursor?->restore();
    }
}
