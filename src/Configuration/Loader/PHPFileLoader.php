<?php

declare(strict_types=1);

namespace Neu\Configuration\Loader;

use Neu\Configuration\Configuration;
use Neu\Configuration\ConfigurationInterface;
use Neu\Configuration\Exception\InvalidConfigurationException;
use Psl\Filesystem;
use Psl\Str;
use Psl\Type;

use function get_debug_type;

/**
 * @implements LoaderInterface<non-empty-string>
 */
final class PHPFileLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    public function load(mixed $resource): ConfigurationInterface
    {
        /** @var array<string, mixed>|mixed $data */
        $data = (static function () use ($resource): mixed {
            /** @psalm-suppress UnresolvableInclude */
            return @require $resource;
        })();

        /** @psalm-suppress MissingThrowsDocblock - none of the inner types are optional. */
        if (!Type\dict(Type\string(), Type\mixed())->matches($data)) {
            throw new InvalidConfigurationException(Str\format(
                'Resource file "%s" returned invalid configuration type, expected "array<string, mixed>", got "%s".',
                $resource,
                get_debug_type($data)
            ));
        }

        return new Configuration($data);
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource): bool
    {
        if (!Type\non_empty_string()->matches($resource) || !Str\ends_with($resource, '.php')) {
            return false;
        }

        return Filesystem\is_file($resource) && Filesystem\is_readable($resource);
    }
}
