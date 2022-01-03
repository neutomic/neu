<?php

declare(strict_types=1);

namespace Neu\Console\Tree;

/**
 * The `AbstractTree` class provides core functionality for building a tree given
 * a data structure.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @implements TreeInterface<Tk, Tv>
 */
abstract class AbstractTree implements TreeInterface
{
    /**
     * Construct a new instance of a tree.
     *
     * @param array<Tk, Tv> $data
     */
    public function __construct(private readonly array $data)
    {
    }

    /**
     * Retrieve the data of the tree.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Render the tree.
     */
    public function render(): string
    {
        return $this->build($this->data);
    }

    /**
     * Recursively build the tree and each branch and prepend necessary markup
     * for the output.
     *
     * @param array<Tk, Tv> $tree
     */
    abstract protected function build(array $tree, string $prefix = ''): string;
}
