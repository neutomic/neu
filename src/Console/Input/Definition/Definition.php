<?php

declare(strict_types=1);

namespace Neu\Console\Input\Definition;

use Neu\Console\Exception;
use Psl\Str;

/**
 * A `Definition` is an object that designates the parameters accepted by
 * the user when executing commands.
 *
 * @template T
 *
 * @implements DefinitionInterface<T>
 */
abstract class Definition implements DefinitionInterface
{
    /**
     * An alternate notation to specify the input as.
     */
    protected string $alias = '';

    /**
     * Flag if the `Definition` has been assigned a value.
     */
    protected bool $exists = false;

    /**
     * The value the user has given the input.
     */
    protected mixed $value = null;

    /**
     * Construct a new instance of an `Definition`.
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
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Set the alias of the `Definition`.
     */
    public function alias(string $alias): self
    {
        if (Str\Byte\length($alias) > Str\Byte\length($this->name)) {
            $this->alias = $this->name;
            $this->name = $alias;
        } else {
            $this->alias = $alias;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormattedName(string $name): string
    {
        if (Str\Byte\length($name) === 1) {
            return '-' . $name;
        }

        return '--' . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getMode(): Mode
    {
        return $this->mode;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the value of the `Definition` as specified by the user.
     *
     * @throws Exception\MissingValueException If the definition has not been assigned a value.
     *
     * @return T
     */
    public function getValue(): mixed
    {
        if ($this->exists) {
            return $this->value;
        }

        throw new Exception\MissingValueException(Str\format('The "%s" definition has not been assigned a value.', $this::class));
    }

    /**
     * Set the value of the `Definition`.
     *
     * @param T $value
     */
    public function assign(mixed $value): static
    {
        $this->value = $value;
        $this->exists = true;

        return $this;
    }
}
