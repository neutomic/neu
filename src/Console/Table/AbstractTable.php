<?php

declare(strict_types=1);

namespace Neu\Console\Table;

use Psl\Iter;
use Psl\Str;

/**
 * The `AbstractTable` class provides the core functionality for building and
 * displaying tabular data.
 */
abstract class AbstractTable implements TableInterface
{
    /**
     * Data structure that holds the width of each column.
     *
     * @var list<int>
     */
    protected array $columnWidths = [];

    /**
     * Data structure holding the header names of each column.
     *
     * @var list<string>
     */
    protected array $headers = [];

    /**
     * Data structure holding the data for each row in the table.
     *
     * @var list<list<string>>
     */
    protected array $rows = [];

    /**
     * {@inheritDoc}
     */
    public function setHeaders(array $headers): self
    {
        $this->setColumnWidths($headers);
        $this->headers = $headers;

        return $this;
    }

    /**
     * Given the row of data, adjust the column width accordingly so that the
     * columns' width is that of the maximum data field size.
     *
     * @param list<string> $row
     */
    protected function setColumnWidths(array $row): self
    {
        foreach ($row as $index => $value) {
            $width = Str\length($value);
            $currentWidth = $this->columnWidths[$index] ?? 0;

            if ($width > $currentWidth) {
                if (Iter\count($this->columnWidths) === $index) {
                    $this->columnWidths[] = $width;
                } else {
                    $this->columnWidths[$index] = $width;
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param list<list<string>> $rows
     */
    public function setRows(array $rows): self
    {
        $this->rows = [];
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    /**
     * Append a new row of data to the end of the existing rows.
     *
     * @param list<string> $row
     */
    public function addRow(array $row): self
    {
        $this->setColumnWidths($row);
        $this->rows[] = $row;

        return $this;
    }
}
