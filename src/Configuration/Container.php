<?php

declare(strict_types=1);

namespace Neu\Configuration;

use Psl\Iter;
use Psl\Str;
use Psl\Type;

use function array_keys;
use function array_merge_recursive;

/**
 * @template Tk of array-key
 *
 * @implements ContainerInterface<Tk>
 */
final class Container implements ContainerInterface
{
    /**
     * @param array<Tk, mixed> $entries
     */
    public function __construct(
        private readonly array $entries,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function has(string|int $index): bool
    {
        return Iter\contains_key($this->entries, $index);
    }

    /**
     * @inheritDoc
     */
    public function get(string|int $index): mixed
    {
        if (!$this->has($index)) {
            throw new Exception\MissingEntryException(Str\format('Entry "%s" does not exist within the container.', $index));
        }

        return $this->entries[$index];
    }

    /**
     * @inheritDoc
     */
    public function string(string|int $index): string
    {
        /** @var mixed $value */
        $value = $this->get($index);

        try {
            return Type\string()->coerce($value);
        } catch (Type\Exception\CoercionException $e) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" value cannot be converted into a string.', $index), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function int(string|int $index): int
    {
        /** @var mixed $value */
        $value = $this->get($index);

        try {
            return Type\int()->coerce($value);
        } catch (Type\Exception\CoercionException $e) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" value cannot be converted into an integer.', $index), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function bool(string|int $index): bool
    {
        /** @var mixed $value */
        $value = $this->get($index);

        if ('true' === $value) {
            return true;
        }

        if ('false' === $value) {
            return false;
        }

        try {
            return Type\bool()->coerce($value);
        } catch (Type\Exception\CoercionException $e) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" value cannot be converted into a boolean.', $index), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function float(string|int $index): float
    {
        /** @var mixed $value */
        $value = $this->get($index);

        try {
            return Type\float()->coerce($value);
        } catch (Type\Exception\CoercionException $e) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" value cannot be converted into a float.', $index), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function container(int|string $index): ContainerInterface
    {
        /** @var mixed $value */
        $value = $this->get($index);

        try {
            return new self(Type\dict(Type\array_key(), Type\mixed())->coerce($value));
        } catch (Type\Exception\CoercionException $e) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" value cannot be converted into a container.', $index), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function document(string|int $index): ContainerInterface
    {
        /** @var mixed $value */
        $value = $this->get($index);

        try {
            return new self(Type\dict(Type\string(), Type\mixed())->coerce($value));
        } catch (Type\Exception\CoercionException $e) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" value cannot be converted into a document.', $index), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function list(int|string $index): ContainerInterface
    {
        /** @var mixed $value */
        $value = $this->get($index);

        try {
            return new self(Type\vec(Type\mixed())->coerce($value));
        } catch (Type\Exception\CoercionException $e) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" value cannot be converted into a list.', $index), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function merge(ContainerInterface $container): static
    {
        /** @var static<Tk> */
        return new self(array_merge_recursive($this->entries, $container->all()));
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->entries;
    }

    /**
     * @inheritDoc
     */
    public function indices(): array
    {
        return array_keys($this->entries);
    }
}
