<?php

declare(strict_types=1);

namespace Neu\Tests\Http\Server\Middleware;

use Neu\Http\Message\RequestInterface;
use Neu\Http\Message\ResponseInterface;
use Neu\Http\Server\Handler\HandlerInterface;
use Neu\Http\Server\Middleware\MiddlewareInterface;
use Neu\Http\Server\Middleware\MiddlewareStack;
use PHPUnit\Framework\TestCase;

final class MiddlewareStackTest extends TestCase
{
    public function testEmptyStack(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);

        $handler->expects(static::once())->method('handle')->with($request)->willReturn($response);

        $stack = new MiddlewareStack();

        static::assertSame($response, $stack->process($request, $handler));
    }

    public function testStack(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $middlewareOne = $this->createMock(MiddlewareInterface::class);
        $middlewareTwo = $this->createMock(MiddlewareInterface::class);

        $handler->expects(static::never())->method('handle');
        $middlewareTwo->expects(static::once())->method('process')->willReturn($response);
        $middlewareOne->expects(static::once())->method('process')->willReturnCallback(
            static fn(RequestInterface $request, HandlerInterface $next): ResponseInterface => $next->handle($request)
        );

        $stack = new MiddlewareStack();
        $stack
            ->push($middlewareOne)
            ->push($middlewareTwo);

        static::assertSame($response, $stack->process($request, $handler));
    }

    public function testHandlerIsCalledIfAllMiddlewareDelegate(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $middlewareOne = $this->createMock(MiddlewareInterface::class);
        $middlewareTwo = $this->createMock(MiddlewareInterface::class);

        $handler->expects(static::once())->method('handle')->with($request)->willReturn($response);
        $middlewareTwo->expects(static::once())->method('process')->willReturnCallback(
            static fn(RequestInterface $request, HandlerInterface $next): ResponseInterface => $next->handle($request)
        );
        $middlewareOne->expects(static::once())->method('process')->willReturnCallback(
            static fn(RequestInterface $request, HandlerInterface $next): ResponseInterface => $next->handle($request)
        );

        $stack = new MiddlewareStack();
        $stack
            ->push($middlewareOne)
            ->push($middlewareTwo);

        static::assertSame($response, $stack->process($request, $handler));
    }
}
