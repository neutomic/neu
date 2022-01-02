<?php

declare(strict_types=1);

namespace Neu\Cache\Exception;

use InvalidArgumentException;

final class InvalidValueException extends InvalidArgumentException implements ExceptionInterface
{
}
