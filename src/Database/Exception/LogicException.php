<?php

declare(strict_types=1);

namespace Neu\Database\Exception;

use LogicException as RootLogicException;

final class LogicException extends RootLogicException implements ExceptionInterface
{
}
