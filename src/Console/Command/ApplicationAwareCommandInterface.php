<?php

declare(strict_types=1);

namespace Neu\Console\Command;

use Neu\Console\Application;

interface ApplicationAwareCommandInterface extends CommandInterface
{
    public function setApplication(Application $application): void;
}
