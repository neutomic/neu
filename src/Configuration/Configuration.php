<?php

declare(strict_types=1);

namespace Neu\Configuration;

use Psl\Iter;
use Psl\Str;

use function array_merge_recursive;

final class Configuration implements ConfigurationInterface
{
    /**
     * @param array<string, mixed> $entries
     */
    public function __construct(
        private readonly array $entries,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function has(string $entry): bool
    {
        return Iter\contains_key($this->entries, $entry);
    }

    /**
     * @inheritDoc
     */
    public function get(string $entry): mixed
    {
        if (!$this->has($entry)) {
            throw new Exception\InvalidEntryException(Str\format('Entry "%s" does not exist within the configuration.', $entry));
        }

        return $this->entries[$entry];
    }

    /**
     * @inheritDoc
     */
    public function getTyped(string $entry, TypeCoercer\TypeCoercerInterface $coercer): mixed
    {
        return $coercer->coerce($entry, $this->get($entry));
    }

    /**
     * @inheritDoc
     */
    public function merge(ConfigurationInterface $configuration): static
    {
        return new self(array_merge_recursive($this->entries, $configuration->all()));
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->entries;
    }
}
