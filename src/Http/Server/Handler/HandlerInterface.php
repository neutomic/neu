<?php

declare(strict_types=1);

namespace Neu\Http\Server\Handler;

use Neu\Http\Message\RequestInterface;
use Neu\Http\Message\ResponseInterface;

/**
 * Handles a server request and produces a response.
 *
 * An HTTP request handler process an HTTP request in order to produce an
 * HTTP response.
 */
interface HandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(RequestInterface $request): ResponseInterface;
}
