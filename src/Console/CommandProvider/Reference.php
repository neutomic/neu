<?php

declare(strict_types=1);

namespace Neu\Console\CommandProvider;

use Neu\Console\Command;

final class Reference
{
    public function __construct(
        public readonly Command\ConfigurationInterface $configuration,
        public readonly Command\CommandInterface $command,
    ) {
    }
}
