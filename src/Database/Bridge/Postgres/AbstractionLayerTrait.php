<?php

declare(strict_types=1);

namespace Neu\Database\Bridge\Postgres;

use Neu\Database\AbstractionLayerConvenienceMethodsTrait;
use Neu\Database\Schema;

/**
 * @require-implements AbstractionLayerInterface
 */
trait AbstractionLayerTrait
{
    use AbstractionLayerConvenienceMethodsTrait;

    /**
     * {@inheritDoc}
     */
    public function createSchemaManager(): Schema\SchemaManagerInterface
    {
        exit('TODO');
    }
}
