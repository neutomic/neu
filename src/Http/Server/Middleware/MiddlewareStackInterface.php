<?php

declare(strict_types=1);

namespace Neu\Http\Server\Middleware;

interface MiddlewareStackInterface extends MiddlewareInterface
{
    /**
     * Push a middleware into the stack.
     */
    public function push(MiddlewareInterface $middleware): static;
}
