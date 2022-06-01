<?php

declare(strict_types=1);

namespace Neu\Database\Query\Expression;

enum CompositionType: string
{
    case Conjunction = 'AND';
    case Disjunction = 'OR';
}
