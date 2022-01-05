<?php

declare(strict_types=1);

namespace Neu\Console\Output\Sequence;

/**
 * @see http://ascii-table.com/ansi-escape-sequences.php
 */
enum Erase: string
{
    case Display = "\033[2J";
    case Line = "\033[K";
}
