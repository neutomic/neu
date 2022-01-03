<?php

declare(strict_types=1);

namespace Neu\Console\Formatter;

use Psl\Iter;
use Psl\Regex;
use Psl\Str;

abstract class AbstractFormatter implements WrappingFormatterInterface
{
    /**
     * @var array<string, array{foreground: ?Style\ForegroundColor, background: ?Style\BackgroundColor, effects: list<Style\Effect>}>
     */
    protected static array $defaultStyles = [];

    /**
     * @var array<string, Style\StyleInterface>
     */
    protected array $styles = [];

    /**
     * @param array<string, Style\StyleInterface> $styles
     */
    public function __construct(
        protected bool $decorated = false,
        array $styles = []
    ) {
        foreach (static::$defaultStyles as $name => $style) {
            $style = new Style\Style($style['background'], $style['foreground'], $style['effects']);
            $this->addStyle($name, $style);
        }

        foreach ($styles as $name => $style) {
            $this->addStyle($name, $style);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDecorated(bool $decorated): self
    {
        $this->decorated = $decorated;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * {@inheritDoc}
     */
    public function addStyle(string $name, Style\StyleInterface $style): self
    {
        $this->styles[Str\lowercase($name)] = $style;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasStyle(string $name): bool
    {
        return Iter\contains_key($this->styles, Str\lowercase($name));
    }

    /**
     * {@inheritDoc}
     */
    public function getStyle(string $name): Style\StyleInterface
    {
        return $this->styles[Str\lowercase($name)];
    }

    /**
     * Escapes "<" special char in given text.
     */
    public static function escape(string $text): string
    {
        $text = Regex\replace($text, "/([^\\\\]?)</", '$1\\<');
        return self::escapeTrailingBackslash($text);
    }

    /**
     * Escapes trailing "\" in given text.
     */
    public static function escapeTrailingBackslash(string $text): string
    {
        if (Str\ends_with($text, '\\')) {
            $len = Str\length($text);
            $text = Str\trim_right($text, '\\');
            $text = Str\replace("\0", '', $text);
            $text .= Str\repeat("\0", $len - Str\length($text));
        }

        return $text;
    }
}
