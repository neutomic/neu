<?php

declare(strict_types=1);

namespace Neu\Console\Output;

enum Type: int {
case Normal = 1;
case Raw = 2;
case Plain = 4;
    }
