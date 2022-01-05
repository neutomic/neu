<?php

declare(strict_types=1);

namespace Neu\Console\Input\Definition;

enum Mode: int
{
    case Optional = 0;
    case Required = 1;
}
