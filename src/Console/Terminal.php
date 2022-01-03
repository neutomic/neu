<?php

declare(strict_types=1);

namespace Neu\Console;

use Psl\Env;
use Psl\IO;
use Psl\Regex;
use Psl\Shell;
use Psl\Str;

final class Terminal implements TerminalInterface
{
    private IO\ReadStreamHandleInterface $inputHandle;
    private IO\WriteStreamHandleInterface $outputHandle;
    private ?IO\WriteStreamHandleInterface $errorHandle;
    private ?int $height;
    private ?int $width;

    public function __construct(
        ?IO\ReadStreamHandleInterface  $inputHandle = null,
        ?IO\WriteStreamHandleInterface $outputHandle = null,
        ?IO\WriteStreamHandleInterface $errorHandle = null,
        ?int                           $height = null,
        ?int                           $width = null,
    ) {
        $this->inputHandle = $inputHandle ?? IO\input_handle();
        $this->outputHandle = $outputHandle ?? IO\output_handle();
        $this->errorHandle = $errorHandle ?? IO\error_handle();
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * {@inheritDoc}
     */
    public function getInputHandle(): IO\ReadStreamHandleInterface
    {
        return $this->inputHandle;
    }

    /**
     * {@inheritDoc}
     */
    public function getOutputHandle(): IO\WriteStreamHandleInterface
    {
        return $this->outputHandle;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorHandle(): ?IO\WriteStreamHandleInterface
    {
        return $this->errorHandle;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeight(): int
    {
        $lines = Env\get_var('LINES');
        if ($lines !== null) {
            $lines = Str\to_int($lines);
            if ($lines !== null) {
                return $lines;
            }
        }

        if ($this->height !== null) {
            return $this->height;
        }

        $dimensions = $this->getDimensionsUsingStty();
        $this->height = $dimensions['height'] ?? self::DEFAULT_HEIGHT;
        $this->width ??= $dimensions['width'] ?? self::DEFAULT_WIDTH;

        return $this->height;
    }

    /**
     * Initializes dimensions using the output of a stty columns line.
     *
     * @return array{width: ?int, height: ?int}
     */
    private function getDimensionsUsingStty(): array
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
     * {@inheritDoc}
     */
    public function getWidth(): int
    {
        $cols = Env\get_var('COLUMNS');
        if ($cols !== null) {
            $cols = Str\to_int($cols);
            if ($cols !== null) {
                return $cols;
            }
        }

        if ($this->width !== null) {
            return $this->width;
        }

        $dimensions = $this->getDimensionsUsingStty();
        $this->width = $dimensions['width'] ?? self::DEFAULT_WIDTH;
        $this->height ??= $dimensions['height'] ?? self::DEFAULT_HEIGHT;

        return $this->width;
    }
}
