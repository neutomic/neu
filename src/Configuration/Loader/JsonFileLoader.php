<?php

declare(strict_types=1);

namespace Neu\Configuration\Loader;

use Neu\Configuration\Configuration;
use Neu\Configuration\ConfigurationInterface;
use Neu\Configuration\Exception\InvalidConfigurationException;
use Psl\File;
use Psl\Filesystem;
use Psl\Json;
use Psl\Str;
use Psl\Type;

/**
 * @implements LoaderInterface<non-empty-string>
 */
final class JsonFileLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     *
     * @throws File\Exception\RuntimeException If unable to read $resource.
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function load(mixed $resource): ConfigurationInterface
    {
        $content = File\read($resource);

        try {
            $data = Json\typed($content, Type\dict(Type\string(), Type\mixed()));
        } catch (Json\Exception\DecodeException $previous) {
            throw new InvalidConfigurationException(
                Str\format('Failed to decode json resource file "%s".', $resource),
                previous: $previous
            );
        }

        return new Configuration($data);
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource): bool
    {
        if (!Type\non_empty_string()->matches($resource) || !Str\ends_with($resource, '.json')) {
            return false;
        }

        return Filesystem\is_file($resource) && Filesystem\is_readable($resource);
    }
}
