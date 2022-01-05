<?php

declare(strict_types=1);

namespace Neu\Console\Command\Traits;

use Neu\Console\Application;
use Neu\Console\Command;

/**
 * @require-implements Command\ApplicationAwareCommandInterface
 */
trait ApplicationAwareCommandTrait
{
    /**
     * The `Application` that is currently running the command.
     */
    protected readonly Application $application;

    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }
}
