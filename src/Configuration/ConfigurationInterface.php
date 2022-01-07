<?php

declare(strict_types=1);

namespace Neu\Configuration;

interface ConfigurationInterface
{
    /**
     * Return whether the given entry exists within the configuration.
     */
    public function has(string $entry): bool;

    /**
     * Retrieve the value of the given entry.
     *
     * @throws Exception\InvalidEntryException If $entry does not exist.
     */
    public function get(string $entry): mixed;

    /**
     * Retrieve the value of the given entry, and coerce its value to the given type.
     *
     * @template T
     *
     * @param TypeCoercer\TypeCoercerInterface<T> $coercer
     *
     * @throws Exception\InvalidEntryException If $entry does not exist, or was unable to coerce the entry value.
     *
     * @return T
     */
    public function getTyped(string $entry, TypeCoercer\TypeCoercerInterface $coercer): mixed;

    /**
     * Merge the current configuration with the given configuration.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the configuration, and MUST return an instance that has the
     * new configuration entries merged.
     */
    public function merge(ConfigurationInterface $configuration): static;

    /**
     * Retrieve all configuration entries.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
