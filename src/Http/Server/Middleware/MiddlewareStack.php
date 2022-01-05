<?php

declare(strict_types=1);

namespace Neu\Http\Server\Middleware;

use Neu\Http\Message\RequestInterface;
use Neu\Http\Message\ResponseInterface;
use Neu\Http\Server\Handler\HandlerInterface;
use Neu\Http\Server\Handler\MiddlewareHandler;
use Psl\DataStructure;

final class MiddlewareStack implements MiddlewareStackInterface
{
    private DataStructure\Stack $stack;

    public function __construct()
    {
        $this->stack = new DataStructure\Stack();
    }

    /**
     * @inheritDoc
     */
    public function process(RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        while ($middleware = $this->stack->pull()) {
            $next = new MiddlewareHandler($middleware, $next);
        }

        return $next->handle($request);
    }

    public function push(MiddlewareInterface $middleware): static
    {
        $this->stack->push($middleware);
    }
}
