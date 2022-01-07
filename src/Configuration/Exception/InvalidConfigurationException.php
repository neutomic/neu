<?php

declare(strict_types=1);

namespace Neu\Configuration\Exception;

use UnexpectedValueException;

final class InvalidConfigurationException extends UnexpectedValueException implements ExceptionInterface
{
}
