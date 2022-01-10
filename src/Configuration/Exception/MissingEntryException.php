<?php

declare(strict_types=1);

namespace Neu\Configuration\Exception;

use InvalidArgumentException;

final class MissingEntryException extends InvalidArgumentException implements ExceptionInterface
{
}
