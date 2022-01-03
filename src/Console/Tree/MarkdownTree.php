<?php

declare(strict_types=1);

namespace Neu\Console\Tree;

use Neu\Console;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

use function is_iterable;

/**
 * Build a tree given an infinitely nested data structure using Markdown syntax.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends AbstractTree<Tk, Tv>
 */
final class MarkdownTree extends AbstractTree
{
    /**
     * {@inheritDoc}
     */
    protected function build(array $tree, string $prefix = ''): string
    {
        $lines = [];
        $keys = Vec\keys($tree);
        $branch = Vec\values($tree);

        for ($i = 0, $count = Iter\count($branch); $i < $count; ++$i) {
            $itemPrefix = $prefix . '<fg=green>-</> ';
            $next = $branch[$i];

            if (is_iterable($branch[$i])) {
                $lines[] = $itemPrefix . $keys[$i];
            } else {
                $lines[] = $itemPrefix . $branch[$i];
            }

            if (is_iterable($next)) {
                $lines[] = $this->build(
                    Dict\from_iterable($next),
                    $prefix . '  ',
                );
            }
        }

        return Str\join($lines, Console\Output\OutputInterface::END_OF_LINE);
    }
}
