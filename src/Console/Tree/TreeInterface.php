<?php

declare(strict_types=1);

namespace Neu\Console\Tree;

/**
 * A `TreeInterface` object will construct the markup for a human-readable of nested data.
 *
 * @template Tk of array-key
 * @template Tv
 */
interface TreeInterface
{
    /**
     * Retrieve the data structure of the `TreeInterface`.
     *
     * @return array<Tk, Tv>
     */
    public function getData(): array;

    /**
     * Build and return the markup for the `TreeInterface`.
     */
    public function render(): string;
}
