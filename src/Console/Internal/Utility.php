<?php

declare(strict_types=1);

namespace Neu\Console\Internal;

use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

/**
 * @internal
 */
enum Utility
{
    /**
     * Finds alternative of $name among $collection.
     *
     * @param list<string> $collection
     *
     * @return list<string>
     */
    public static function findAlternatives(string $name, array $collection): array
    {
        $threshold = 1e3;
        $alternatives = [];
        $collectionParts = [];
        foreach ($collection as $item) {
            $collectionParts[$item] = Str\split($item, ':');
        }

        foreach (Str\split($name, ':') as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = Iter\contains_key($alternatives, $collectionName);
                if (!Iter\contains_key($parts, $i)) {
                    if ($exists) {
                        $alternatives[$collectionName] += $threshold;
                    }

                    continue;
                }

                $lev = (float)Str\levenshtein($subname, $parts[$i]);
                if ($lev <= Str\length($subname) / 3 || ('' !== $subname && Str\contains($parts[$i], $subname))) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }

        foreach ($collection as $item) {
            $lev = (float)Str\levenshtein($name, $item);
            if ($lev <= Str\length($name) / 3 || Str\contains($item, $name)) {
                $alternatives[$item] = Iter\contains_key($alternatives, $item) ? $alternatives[$item] - $lev : $lev;
            }
        }

        return Vec\keys(Dict\sort(Dict\filter($alternatives, static fn($lev) => $lev < (2 * $threshold))));
    }
}
