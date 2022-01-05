<?php

declare(strict_types=1);

namespace Neu\Console\UserInput;

use Psl\Iter;
use Psl\Str;
use Psl\Vec;

/**
 * The `Menu` class presents the user with a prompt and a list of available
 * options to choose from.
 *
 * @extends AbstractUserInput<string>
 */
final class Menu extends AbstractUserInput
{
    /**
     * {@inheritDoc}
     */
    public function prompt(string $message): string
    {
        $keys = Vec\keys($this->acceptedValues);
        $values = Vec\values($this->acceptedValues);

        $cursor = null;
        if ($this->position !== null) {
            [$column, $row] = $this->position;
            $cursor = $this->output->getCursor();
            $cursor->save();
            $cursor->move($column, $row);
        }

        $this->output->writeLine(Str\format('<fg=green>%s</>', $message));
        $this->output->writeLine('');
        foreach ($values as $index => $item) {
            $this->output->writeLine(Str\format('  [<fg=yellow>%d</>] %s', $index + 1, (string)$item));
        }

        if ($this->default !== '' && !$this->input->isInteractive()) {
            $result = $this->acceptedValues[$this->default];
        } else {
            $this->output->writeLine('');
            $result = $this->selection($values, $keys);
        }

        $cursor?->restore();

        return $result;
    }

    /**
     * @param array<string, string> $dictionary
     * @param array<int, string> $values
     * @param array<int, string> $keys
     */
    private function selection(array $dictionary, array $values, array $keys): string
    {
        $this->output->write('<fg=green>↪</> ');
        $input = $this->input->getUserInput();
        if ($this->default !== '' && $input !== '') {
            return $dictionary[$this->default];
        }

        $input = Str\to_int($input);
        if ($input !== null) {
            $input--;

            if (Iter\contains_key($values, $input)) {
                return $keys[$input];
            }

            if ($input < 0 || $input >= Iter\count($values)) {
                $this->output->writeLine('<fg=red>Invalid menu selection</>');
            }
        } else {
            $this->output->writeLine('<fg=red>Invalid menu selection.</>');
        }

        return $this->selection($values, $keys);
    }
}
