<?php

declare(strict_types=1);

namespace Neu\Database\Query\Builder\Internal;

enum JoinType: string
{
case Inner = 'INNER';
case Left = 'LEFT';
case Right = 'RIGHT';
    }
