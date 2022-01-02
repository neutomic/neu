<?php

declare(strict_types=1);

namespace Neu\Cache\Exception;

use InvalidArgumentException;

final class InvalidKeyException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forEmptyKey(): self
    {
        return new self('Cache key must not be empty.');
    }
}
