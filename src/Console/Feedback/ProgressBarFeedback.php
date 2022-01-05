<?php

declare(strict_types=1);

namespace Neu\Console\Feedback;

use Neu\Console;
use Neu\Console\Output;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

/**
 * The `ProgressBarFeedback` class displays feedback information with a progress bar.
 * Additional information including percentage done, time elapsed, and time
 * remaining is included by default.
 */
final class ProgressBarFeedback extends AbstractFeedback
{
    /**
     * The 2-string character format to use when constructing the displayed bar.
     */
    protected array $characterSequence = ['=', '>', ' '];

    /**
     * {@inheritDoc}
     */
    public function setCharacterSequence(array $characters): self
    {
        if (Iter\count($characters) !== 3) {
            throw new Console\Exception\InvalidCharacterSequenceException(
                'Display bar must only contain 3 values',
            );
        }

        return parent::setCharacterSequence($characters);
    }

    /**
     * {@inheritDoc}
     */
    protected function display(bool $finish = false): void
    {
        if (!$finish && $this->current === $this->total) {
            return;
        }

        $completed = $this->getPercentageComplete();
        $variables = $this->buildOutputVariables();
        if ($finish) {
            $variables['estimated'] = $variables['elapsed'];
        }

        // Need to make prefix and suffix before the bar, so we know how long to render it.
        $prefix = $this->insert($this->prefix, $variables);
        $prefixLength = Str\length(
            $this->output->format($prefix, Output\Type::Plain),
        );

        $suffix = $this->insert($this->suffix, $variables);
        $suffixLength = Str\length(
            $this->output->format($suffix, Output\Type::Plain),
        );

        if (!$this->output->isDecorated()) {
            return;
        }

        $width = Console\Terminal::getWidth();
        $size = $width - ($prefixLength + $suffixLength);
        if ($size < 0) {
            $size = 0;
        }

        $completed = (int)Math\floor($completed * $size);
        $rest = $size - ($completed + Str\length($this->characterSequence[1]));

        // Str\slice is needed to trim off the bar cap at 100%
        $bar = Str\slice(
            Str\repeat($this->characterSequence[0], $completed) . $this->characterSequence[1] . Str\repeat($this->characterSequence[2], $rest < 0 ? 0 : $rest),
            0,
            $size
        );

        $variables = [
            'prefix' => $prefix,
            'feedback' => $bar,
            'suffix' => $suffix,
        ];

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
