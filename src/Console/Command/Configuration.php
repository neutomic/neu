<?php

declare(strict_types=1);

namespace Neu\Console\Command;

use Attribute;
use Neu\Console\Exception;
use Neu\Console\Input\Bag;
use Psl\Regex;
use Psl\Str;

#[Attribute(Attribute::TARGET_CLASS)]
final class Configuration implements ConfigurationInterface
{
    public function __construct(
        /**
         * The name of the command passed into the command line.
         *
         * @var non-empty-string
         */
        private readonly string $name,
        /**
         * The description of the command used when rendering its help screen.
         *
         * @var non-empty-string
         */
        private readonly string $description = '',
        /**
         * The aliases for the command name.
         *
         * @var list<string>
         */
        private readonly array $aliases = [],
        /**
         * Bag container holding all registered `Flag` objects.
         */
        private readonly Bag\FlagBag $flags = new Bag\FlagBag(),
        /**
         * Bag container holding all registered `Option` objects.
         */
        private readonly Bag\OptionBag $options = new Bag\OptionBag(),
        /**
         * Bag container holding all registered `Argument` objects.
         */
        private readonly Bag\ArgumentBag $arguments = new Bag\ArgumentBag(),
        /**
         * Whether the command should be publicly shown or not.
         */
        private readonly bool $hidden = false,
    ) {
        if (!Regex\matches($name, "/^[^\:]++(\:[^\:]++)*$/")) {
            throw new Exception\InvalidCharacterSequenceException(
                Str\format('Command name "%s" is invalid.', $name),
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @inheritDoc
     */
    public function getFlags(): Bag\FlagBag
    {
        return $this->flags;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): Bag\OptionBag
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function getArguments(): Bag\ArgumentBag
    {
        return $this->arguments;
    }

    /**
     * @inheritDoc
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
