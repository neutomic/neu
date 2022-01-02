<?php

declare(strict_types=1);

namespace Neu\Cache\Exception;

use Psl\Str;
use RuntimeException;

final class UnavailableItemException extends RuntimeException implements ExceptionInterface
{
    /**
     * Create an {@see UnavailableItemException} for the given key.
     *
     * @param non-empty-string $key
     */
    public static function for(string $key): self
    {
        return new self(Str\format('No cache item is associated with the "%s" key.', $key));
    }
}
