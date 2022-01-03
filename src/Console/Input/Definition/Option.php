<?php

declare(strict_types=1);

namespace Neu\Console\Input\Definition;

use Psl\Str;

/**
 * An `Option` is a value parameter specified by a user.
 *
 * @extends Definition<string>
 */
final class Option extends Definition
{
    /**
     * Construct a new `Option` object.
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
        bool             $aliased = true,
    ) {
        parent::__construct($name, $description, $mode);

        if ($aliased && Str\length($name) > 1) {
            $this->alias(Str\slice($name, 0, 1));
        }
    }
}
