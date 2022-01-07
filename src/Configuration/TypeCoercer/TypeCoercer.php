<?php

declare(strict_types=1);

namespace Neu\Configuration\TypeCoercer;

use Neu\Configuration\Exception;
use Psl\Str;
use Psl\Type;

/**
 * @template-covariant T
 *
 * @implements TypeCoercerInterface<T>
 */
final class TypeCoercer implements TypeCoercerInterface
{
    /**
     * @param Type\TypeInterface<T> $type
     */
    public function __construct(
        private readonly Type\TypeInterface $type,
    ) {
    }

    public static function of(Type\TypeInterface $type): static
    {
        return new static($type);
    }

    /**
     * @inheritDoc
     */
    public function coerce(string $name, mixed $entry): mixed
    {
        try {
            return $this->type->coerce($entry);
        } catch (Type\Exception\CoercionException $previous) {
            throw new Exception\InvalidEntryException(Str\format(
                'Failed to coerce entry "%s" to "%s" type.',
                $name,
                $this->type->toString(),
            ), 0, $previous);
        }
    }
}
