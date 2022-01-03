<?php

declare(strict_types=1);

namespace Neu\Console\UserInput;

use Psl\Iter;
use Psl\Str;

/**
 * @extends AbstractUserInput<bool>
 */
final class Confirm extends AbstractUserInput
{
    /**
     * The message to be appended to the prompt message containing the accepted
     * values.
     */
    protected string $message = ' [y/n]: ';
    /**
     * Input values accepted to continue.
     *
     * @var array<string, bool>
     */
    protected array $acceptedValues = [
        'y' => true,
        'yes' => true,
        'oui' => true,
        'n' => false,
        'no' => false,
        'non' => false,
    ];

    /**
     * {@inheritDoc}
     */
    public function prompt(string $message): bool
    {
        $output = $message . ' ' . $this->message . ' ';

        $cursor = null;
        if ($this->position !== null) {
            [$column, $row] = $this->position;
            $cursor = $this->output->getCursor();
            $cursor->save();
            $cursor->move($column, $row);
        }

        $this->output->write($output);
        $input = $this->input->getUserInput();
        if ('' === $input && '' !== $this->default) {
            $input = $this->default;
        }

        if (!Iter\contains_key($this->acceptedValues, Str\lowercase($input))) {
            return $this->prompt($message);
        }

        $cursor?->restore();

        return $this->acceptedValues[Str\lowercase($input)];
    }

    /**
     * {@inheritDoc}
     */
    public function setDefault(string $default = ''): self
    {
        switch (Str\lowercase($default)) {
            case 'y':
            case 'yes':
                $this->default = $default;
                $message = ' [<fg=yellow>Y</>/n]: ';
                break;
            case 'n':
            case 'no':
                $this->default = $default;
                $message = ' [y/<fg=yellow>N</>]: ';
                break;
            case 'non':
                $this->default = $default;
                $message = ' [o/<fg=yellow>N</>]: ';
                break;
            case 'oui':
                $this->default = $default;
                $message = ' [<fg=yellow>O</>/n]: ';
                break;
            default:
                $message = ' [y/n]: ';
                break;
        }

        $this->message = $message;

        return $this;
    }
}
