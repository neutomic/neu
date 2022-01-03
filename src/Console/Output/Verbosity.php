<?php

declare(strict_types=1);

namespace Neu\Console\Output;

enum Verbosity: int {
case Quite = 16;
case Normal = 32;
case Verbose = 64;
case VeryVerbose = 128;
case Debug = 256;
    }
