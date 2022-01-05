<?php

declare(strict_types=1);

namespace Neu\Console;

use Psl\Env;
use Psl\Regex;
use Psl\Shell;
use Psl\Str;

final class Terminal
{
    private static ?int $height = null;
    private static ?int $width = null;

    /**
     * Get the terminal width.
     */
    public static function getHeight(): int
    {
        $lines = Env\get_var('LINES');
        if ($lines !== null) {
            $lines = Str\to_int($lines);
            if ($lines !== null) {
                return $lines;
            }
        }

        if (self::$height !== null) {
            return self::$height;
        }

        $dimensions = self::getDimensionsUsingStty();
        self::$height = $dimensions['height'] ?? self::DEFAULT_HEIGHT;
        self::$width ??= $dimensions['width'] ?? self::DEFAULT_WIDTH;

        return self::$height;
    }

    /**
     * Initializes dimensions using the output of a stty columns line.
     *
     * @return array{width: ?int, height: ?int}
     */
    private static function getDimensionsUsingStty(): array
    {
        try {
            $sttyString = Shell\execute('stty', ['-a', '|', 'grep', 'columns'], escape_arguments: false);

            if ($matches = Regex\first_match($sttyString, "/rows.(\d+);.columns.(\d+);/i")) {
                // extract [w, h] from "rows h; columns w;"
                return [
                    'width' => Str\to_int($matches[2]),
                    'height' => Str\to_int($matches[1]),
                ];
            }

            if ($matches = Regex\first_match($sttyString, "/;.(\d+).rows;.(\d+).columns/i")) {
                // extract [w, h] from "; h rows; w columns"
                return [
                    'width' => Str\to_int($matches[2]),
                    'height' => Str\to_int($matches[1]),
                ];
            }

            return ['width' => null, 'height' => null];
        } catch (Shell\Exception\FailedExecutionException) {
            return ['width' => null, 'height' => null];
        }
    }

    /**
     * Get the terminal width.
     */
    public static function getWidth(): int
    {
        $cols = Env\get_var('COLUMNS');
        if ($cols !== null) {
            $cols = Str\to_int($cols);
            if ($cols !== null) {
                return $cols;
            }
        }

        if (self::$width !== null) {
            return self::$width;
        }

        $dimensions = self::getDimensionsUsingStty();
        self::$width = $dimensions['width'] ?? self::DEFAULT_WIDTH;
        self::$height ??= $dimensions['height'] ?? self::DEFAULT_HEIGHT;

        return self::$width;
    }
}
