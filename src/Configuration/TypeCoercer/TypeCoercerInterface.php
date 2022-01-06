<?php

declare(strict_types=1);

namespace Neu\Configuration\TypeCoercer;

use Neu\Configuration\Exception;

/**
 * @template-covariant T
 */
interface TypeCoercerInterface
{
    /**
     * @param mixed $entry
     *
     * @throws Exception\InvalidEntryException If $entry cannot be coerced to the expected type.
     *
     * @return T
     */
    public function coerce(string $name, mixed $entry): mixed;
}
