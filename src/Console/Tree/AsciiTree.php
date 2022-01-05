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
 * Build a human-readable ASCII tree given an infinitely nested data structure.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends AbstractTree<Tk, Tv>
 */
final class AsciiTree extends AbstractTree
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
            $itemPrefix = $prefix;
            $next = $branch[$i];

            if ($i === $count - 1) {
                if (is_iterable($next)) {
                    $itemPrefix .= $prefix === ''
                        ? '<fg=green>──┬</> '
                        : '<fg=green>└─┬</> ';
                } else {
                    $itemPrefix .= $prefix === ''
                        ? '<fg=green>───</> '
                        : '<fg=green>└──</> ';
                }
            } elseif (is_iterable($next)) {
                $itemPrefix .= '<fg=green>├─┬</> ';
            } else {
                $itemPrefix .= (0 === $i && '' === $prefix)
                    ? '<fg=green>┌──</> '
                    : '<fg=green>├──</> ';
            }


            if (is_iterable($branch[$i])) {
                $lines[] = $itemPrefix . $keys[$i];
            } else {
                $lines[] = $itemPrefix . $branch[$i];
            }

            if (is_iterable($next)) {
                $lines[] = $this->build(
                    Dict\from_iterable($next),
                    $prefix . ($i === $count - 1 ? '  ' : '<fg=green>│</> '),
                );
            }
        }

        return Str\join($lines, Console\Output\OutputInterface::END_OF_LINE);
    }
}
