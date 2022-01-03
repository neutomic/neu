<?php

declare(strict_types=1);

namespace Neu\Console\Input\Definition;

use Psl\Str;

/**
 * A `Flag` is a boolean parameter (denoted by an integer) specified by a user.
 *
 * @extends Definition<int>
 */
final class Flag extends Definition
{
    /**
     * The negative alias of the `Flag` (i.e., --no-foo for -foo). A negative
     * value is only available if a 'long' `Flag` name is available.
     */
    private string $negativeAlias = '';

    /**
     * Construct a new `Flag` object.
     */
    public function __construct(
        /**
         * The name and primary method to specify the input.
         */
        protected string $name,
        /**
         * The description of the input.
         */
        protected string $description = '',
        /**
         * The mode of the input to determine if it should be required by the user.
         */
        protected Mode   $mode = Mode::Optional,
        /**
         * Whether the flag is stackable or not (i.e., -fff is given a value of 3).
         */
        private bool $stackable = false,
    ) {
        parent::__construct($name, $description, $mode);

        if (Str\length($name) > 1) {
            $this->negativeAlias = 'no-' . $name;
        }
    }

    /**
     * Retrieve the negative alias of the `Flag` or null of none.
     */
    public function getNegativeAlias(): string
    {
        return $this->negativeAlias;
    }

    /**
     * If the `Flag` is stackable, increase its value for each occurrence of the
     * flag.
     */
    public function increaseValue(): self
    {
        $this->exists = true;
        if ($this->stackable) {
            if ($this->value === null) {
                $this->value = 1;
            } else {
                $this->value++;
            }
        }

        return $this;
    }

    /**
     * Retrieve whether the `Flag` is stackable or not.
     */
    public function isStackable(): bool
    {
        return $this->stackable;
    }

    /**
     * Set whether the `Flag` is stackable or not.
     */
    public function setStackable(bool $stackable): self
    {
        $this->stackable = $stackable;

        return $this;
    }

    /**
     * Set an alias for the `Flag`. If the 'name' given at construction is a short
     * name and the alias set is long, the 'alias' given here will serve as the
     * 'name' and the original name will be set to the 'alias'.
     */
    public function alias(string $alias): self
    {
        parent::alias($alias);

        if (Str\length($this->getName()) > 1) {
            $this->negativeAlias = 'no-' . $this->getName();
        }

        return $this;
    }
}
