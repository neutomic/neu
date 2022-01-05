<?php

declare(strict_types=1);

namespace Neu\Console\Table;

use Neu\Console;
use Psl\Str;

/**
 * The `TabDelimitedTable` class builds and outputs a table with values tab-delimited
 * for use in other applications.
 */
final class TabDelimitedTable extends AbstractTable
{
    /**
     * Build the table and return its markup.
     */
    public function render(): string
    {
        $output = [];
        $output[] = Str\join($this->headers, "\t");

        foreach ($this->rows as $row) {
            $output[] = Str\join($row, "\t");
        }

        return Str\trim(Str\join($output, Console\Output\OutputInterface::END_OF_LINE));
    }
}
