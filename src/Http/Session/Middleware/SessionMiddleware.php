<?php

declare(strict_types=1);

namespace Neu\Http\Session\Middleware;

use Neu\Http\Message\RequestInterface;
use Neu\Http\Message\ResponseInterface;
use Neu\Http\Server\Handler\HandlerInterface;
use Neu\Http\Server\Middleware\MiddlewareInterface;
use Neu\Http\Session\Initializer\InitializerInterface;
use Neu\Http\Session\Persistence\PersistenceInterface;

final class SessionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly InitializerInterface $initializer,
        private readonly PersistenceInterface $persistence,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $request = $this->initializer->initialize($request);

        return $this->persistence->persist($request, $next->handle($request));
    }
}
