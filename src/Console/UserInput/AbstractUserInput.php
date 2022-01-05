<?php

declare(strict_types=1);

namespace Neu\Console\UserInput;

use Neu\Console;

/**
 * `AbstractUserInput` handles core functionality for prompting and accepting
 * the user input.
 *
 * @template T
 *
 * @implements UserInputInterface
 */
abstract class AbstractUserInput implements UserInputInterface
{
    /**
     * Input values accepted to continue.
     *
     * @var array<string, T>
     */
    protected array $acceptedValues = [];

    /**
     * Default value if input given is empty.
     */
    protected string $default = '';

    /**
     * If the input should be accepted strictly or not.
     */
    protected bool $strict = true;

    /**
     * Display position.
     *
     * @var null|array{0: int, 1: int}
     */
    protected ?array $position = null;

    /**
     * Construct a new `UserInput` object.
     */
    public function __construct(
        /**
         * The `InputInterface` object used for retrieving user input.
         */
        protected Console\Input\InputInterface   $input,
        /**
         * The `OutputInterface` object used for sending output.
         */
        protected Console\Output\OutputInterface $output,
    ) {
    }

    /**
     * Set the display position (column, row).
     *
     * Implementation should not change position unless this method
     * is called.
     *
     * When changing positions, the implementation should always save the cursor
     * position, then restore it.
     *
     * @param null|array{0: int, 1: int}
     */
    public function setPosition(?array $position): void
    {
        $this->position = $position;
    }

    /**
     * Set the values accepted by the user.
     *
     * @param array<string, T> $values
     */
    public function setAcceptedValues(array $values = []): self
    {
        $this->acceptedValues = $values;

        return $this;
    }

    /**
     * Set the default value to use when input is empty.
     */
    public function setDefault(string $default): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Set if the prompt should run in strict mode.
     */
    public function setStrict(bool $strict): self
    {
        $this->strict = $strict;

        return $this;
    }
}
