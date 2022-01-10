<?php

declare(strict_types=1);

namespace Neu\Configuration;

/**
 * @template Tk of array-key
 */
interface ContainerInterface
{
    /**
     * Return whether an entry with the given index exists within this container.
     *
     * @param Tk $index
     */
    public function has(string|int $index): bool;

    /**
     * Retrieve the entry value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function get(string|int $index): mixed;

    /**
     * Retrieve the entry string value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a string.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function string(string|int $index): string;

    /**
     * Retrieve the entry integer value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into an integer.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function int(string|int $index): int;

    /**
     * Retrieve the entry boolean value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a boolean.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function bool(string|int $index): bool;

    /**
     * Retrieve the entry float value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a float.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function float(string|int $index): float;

    /**
     * Retrieve the entry container value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a container.
     *
     * @return ContainerInterface<array-key>
     */
    public function container(string|int $index): ContainerInterface;

    /**
     * Retrieve the entry document value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a document.
     *
     * @return ContainerInterface<string>
     */
    public function document(string|int $index): ContainerInterface;

    /**
     * Retrieve the entry list value using its index.
     *
     * @param Tk $index
     *
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a list.
     *
     * @return ContainerInterface<int>
     */
    public function list(string|int $index): ContainerInterface;

    /**
     * Merge the current container with the given container.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the configuration, and MUST return an instance that has the
     * new configuration entries merged.
     *
     * @param ContainerInterface<Tk> $container
     *
     * @return static<Tk>
     */
    public function merge(ContainerInterface $container): static;

    /**
     * Return a list of all entry indices present in the current container.
     *
     * @return list<Tk>
     */
    public function indices(): array;

    /**
     * Retrieve all entries within this container.
     *
     * @return array<Tk, mixed>
     */
    public function all(): array;
}
