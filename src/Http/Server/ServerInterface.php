<?php

declare(strict_types=1);

namespace Neu\Http\Server;

use Neu\Http\Server\Handler\HandlerInterface;

/**
 * The `HttpServerInterface` provides an interface implemented by all HTTP servers.
 */
interface ServerInterface
{
    /**
     * Start serving connection(s) with the provided handler.
     */
    public function serve(HandlerInterface $handler): void;
}
