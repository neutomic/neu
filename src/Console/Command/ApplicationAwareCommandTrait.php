<?php

declare(strict_types=1);

namespace Neu\Console\Command;

use Neu\Console\Application;

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
