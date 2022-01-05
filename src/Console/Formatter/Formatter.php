<?php

declare(strict_types=1);

namespace Neu\Console\Formatter;

use Neu\Console;
use Psl\Dict;
use Psl\Iter;
use Psl\Regex;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;

use function preg_match_all;

use const PREG_OFFSET_CAPTURE;

final class Formatter extends AbstractFormatter
{
    /**
     * @var array<string, array{foreground: ?Style\ForegroundColor, background: ?Style\BackgroundColor, effects: list<Style\Effect>}>
     */
    protected static array $defaultStyles = [
        'comment' => [
            'foreground' => Style\ForegroundColor::Yellow,
            'background' => null,
            'effects' => [],
        ],
        'success' => [
            'foreground' => Style\ForegroundColor::Green,
            'background' => null,
            'effects' => [],
        ],
        'warning' => [
            'foreground' => Style\ForegroundColor::Black,
            'background' => Style\BackgroundColor::Yellow,
            'effects' => [],
        ],
        'info' => [
            'foreground' => Style\ForegroundColor::Blue,
            'background' => null,
            'effects' => [],
        ],
        'question' => [
            'foreground' => Style\ForegroundColor::Cyan,
            'background' => Style\BackgroundColor::Black,
            'effects' => [],
        ],
        'error' => [
            'foreground' => Style\ForegroundColor::White,
            'background' => Style\BackgroundColor::Red,
            'effects' => [],
        ],
    ];

    private Style\StyleStack $styleStack;

    /**
     * @param array<string, Style\StyleInterface> $styles
     */
    public function __construct(bool $decorated = false, array   $styles = [])
    {
        parent::__construct($decorated, $styles);

        $this->styleStack = new Style\StyleStack();
    }

    /**
     * {@inheritDoc}
     */
    public function format(string $message, int $width = 0): string
    {
        $offset = 0;
        $output = '';
        $currentLineLength = 0;
        preg_match_all(
            '#<(([a-z][^<>]*+) | /([a-z][^<>]*+)?)>#ix',
            $message,
            $matches,
            PREG_OFFSET_CAPTURE,
        );

        foreach ($matches[0] as $i => $match) {
            $pos = (int)$match[1];
            $text = $match[0];
            if (0 !== $pos && '\\' === $message[$pos - 1]) {
                continue;
            }

            // add the text up to the next tag
            [$decorated_text, $currentLineLength] = $this->applyCurrentStyle(Byte\slice($message, $offset, $pos - $offset), $output, $width, $currentLineLength);
            $output .= $decorated_text;
            $offset = $pos + Byte\length($text);
            // opening tag?
            $open = '/' !== $text[1];
            if ($open) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = $matches[3][$i][0] ?? '';
            }

            if (!$open && !$tag) {
                // </>
                $this->styleStack->pop();
            } else {
                $style = $this->createStyleFromString($tag);
                if ($style === null) {
                    [$decorated_text, $currentLineLength] = $this->applyCurrentStyle($text, $output, $width, $currentLineLength);
                    $output .= $decorated_text;
                } elseif ($open) {
                    $this->styleStack->push($style);
                } else {
                    $this->styleStack->pop($style);
                }
            }
        }

            [$decorated_text] = $this->applyCurrentStyle(Byte\slice($message, $offset), $output, $width, $currentLineLength);
            $output .= $decorated_text;

        if (Byte\contains($output, "\0")) {
            $output = Byte\replace($output, "\0", '\\');
        }

        return Byte\replace($output, '\\<', '<');
    }

    /**
     * Applies current style from stack to text, if it must be applied.
     *
     * @return array{0: string, 1: int}
     */
    private function applyCurrentStyle(string $text, string $current, int $width, int $currentLineLength): array
    {
        if ('' === $text) {
            return ['', $currentLineLength];
        }

        if (0 <= $width) {
            return [
                $this->isDecorated() ? $this->styleStack->getCurrent()->apply($text) : $text,
                $currentLineLength,
            ];
        }

        if (0 === $currentLineLength && '' !== $current) {
            $text = Byte\trim_left($text);
        }

        if ($currentLineLength > 0 && $width > $currentLineLength) {
            $i = $width - $currentLineLength;
            $prefix = Byte\slice($text, 0, $i) . "\n";
            $text = Byte\slice($text, $i);
        } else {
            $prefix = '';
        }

        $matches = Regex\first_match($text, "~(\\n)$~");
        $text = $prefix . Regex\replace($text, '~([^\\n]{' . $width . '})\\ *~', "\$1\n");
        $text = Byte\trim_right($text, "\n") . ($matches[1] ?? '');
        if (!$currentLineLength && '' !== $current && "\n" !== Byte\slice($current, -1)) {
            $text = "\n" . $text;
        }

        $lines = Byte\split($text, "\n");
        foreach ($lines as $line) {
            $currentLineLength += Byte\length($line);
            if ($width <= $currentLineLength) {
                $currentLineLength = 0;
            }
        }

        if ($this->isDecorated()) {
            foreach ($lines as $i => $line) {
                $lines[$i] = $this->styleStack->getCurrent()->apply($line);
            }
        }

        return [Str\join($lines, "\n"), $currentLineLength];
    }

    /**
     * Tries to create new style instance from string.
     */
    private function createStyleFromString(string $string): ?Style\StyleInterface
    {
        if (Iter\contains_key($this->styles, $string)) {
            return $this->styles[$string];
        }

        $attributes = Byte\split(Byte\trim(Byte\replace($string, ';', ' ')), ' ');
        if (Iter\is_empty($attributes)) {
            return null;
        }

        $style = new Style\Style();
        $valid = false;

        $backgrounds = Dict\reindex(Style\BackgroundColor::cases(), static fn(Style\BackgroundColor $enum) => $enum->name);
        $foregrounds = Dict\reindex(Style\ForegroundColor::cases(), static fn(Style\ForegroundColor $enum) => $enum->name);
        $effects = Dict\reindex(Style\Effect::cases(), static fn(Style\Effect $enum) => $enum->name);

        $parse_attribute_value = static function(string $attribute): string {
            if (Byte\contains($attribute, '=')) {
                [$_, $value] = Byte\split($attribute, '=', 2);
            } else {
                $value = $attribute;
            }

            $value = Byte\replace_every($value, ['"' => '', '\'' => '', '-' => ' ']);

            return Str\join(Vec\map(Byte\split($value, ' '), Byte\capitalize(...)), '');
        };

        foreach ($attributes as $attribute) {
            if (Byte\starts_with($attribute, 'bg=') || Byte\starts_with($attribute, 'background=')) {
                $background = $parse_attribute_value($attribute);
                if ('' === $background) {
                    continue;
                }

                if ('Random' === $background) {
                    $background = Iter\random(Vec\keys($backgrounds));
                } elseif (!Iter\contains_key($backgrounds, $background)) {
                    throw new Console\Exception\InvalidCharacterSequenceException(
                        Str\format('Background "%s" does not exists.', $background),
                    );
                }

                $valid = true;
                $style->setBackground($backgrounds[$background]);
                continue;
            }

            if (Byte\starts_with($attribute, 'fg=') || Byte\starts_with($attribute, 'foreground=')) {
                $foreground = $parse_attribute_value($attribute);
                if ('' === $foreground) {
                    continue;
                }

                if ('Random' === $foreground) {
                    $foreground = Iter\random(Vec\keys($foregrounds));
                } elseif (!Iter\contains_key($foregrounds, $foreground)) {
                    throw new Console\Exception\InvalidCharacterSequenceException(
                        Str\format('Foreground "%s" does not exists.', $foreground),
                    );
                }

                $valid = true;
                $style->setForeground($foregrounds[$foreground]);
                continue;
            }

            $effect = $parse_attribute_value($attribute);
            if (!Iter\contains_key($effects, $effect)) {
                continue;
            }

            $valid = true;
            $style->setEffect($effects[$effect]);
        }

        return $valid ? $style : null;
    }
}
