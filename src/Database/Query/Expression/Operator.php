<?php

declare(strict_types=1);

namespace Neu\Database\Query\Expression;

enum Operator: string
{
    case Equal  = '=';
    case NotEqual = '<>';
    case LowerThan  = '<';
    case LowerThanOrEqual = '<=';
    case GreaterThan  = '>';
    case GreaterThanOrEqual = '>=';
}
