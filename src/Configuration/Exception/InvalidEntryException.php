<?php

declare(strict_types=1);

namespace Neu\Configuration\Exception;

use UnexpectedValueException;

final class InvalidEntryException extends UnexpectedValueException implements ExceptionInterface
{
}
