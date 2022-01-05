<?php

declare(strict_types=1);

namespace Neu\Console\Table;

/**
 * A `Table` object will construct the markup for a human-readable (or otherwise
 * parsable) representation of tabular data.
 */
interface TableInterface
{
    /**
     * Set the column names for the table.
     *
     * @param list<string> $headers
     */
    public function setHeaders(array $headers): self;

    /**
     * Set the data for the rows in the table with A vector containing a vec
     * for each row in the table.
     *
     * @param list<list<string>> $rows
     */
    public function setRows(array $rows): self;

    /**
     * Add a row of data to the end of the existing data.
     *
     * @param list<string> $row
     */
    public function addRow(array $row): self;

    /**
     * Build and return the markup for the `Table`.
     */
    public function render(): string;
}
