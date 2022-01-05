<?php

declare(strict_types=1);

namespace Neu\Http\Server\Handler;

use Neu\Http\Message\RequestInterface;
use Neu\Http\Message\ResponseInterface;
use Neu\Http\Server\Middleware\MiddlewareInterface;

final class MiddlewareHandler implements HandlerInterface
{
    public function __construct(
        private readonly MiddlewareInterface $middleware,
        private readonly HandlerInterface $next
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->next);
    }
}
