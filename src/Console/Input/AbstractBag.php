<?php

declare(strict_types=1);

namespace Neu\Console\Input;

use Neu\Console\Bag;

/**
 * @template T of Definition\DefinitionInterface
 *
 * @extends Bag\AbstractBag<string, T>
 */
class AbstractBag extends Bag\AbstractBag
{
    /**
     * @param list<T> $data
     */
    public function __construct(array $data = [])
    {
        $raw = [];
        foreach ($data as $definition) {
            $raw[$definition->getName()] = $definition;
            $raw[$definition->getAlias()] = $definition;
        }

        parent::__construct($raw);
    }

    /**
     * Retrieve the definition object based on the given key.
     *
     * The key is checked against all available names as well as aliases.
     *
     * @param string $key
     * @param null|T $default
     *
     * @return T
     */
    public function get(string|int $key, mixed $default = null): mixed
    {
        $value = parent::get($key, $default);
        if ($value === null) {
            foreach ($this as $definition) {
                if ($key === $definition->getAlias()) {
                    /** @var T */
                    return $definition;
                }
            }
        }

        /** @var T */
        return $value;
    }
}
