<?php

declare(strict_types=1);

namespace Neu\Database\Exception;

use InvalidArgumentException;

final class InvalidQueryException extends InvalidArgumentException implements ExceptionInterface
{
}
