<?php

declare(strict_types=1);

namespace Neu\Http\Session\Initializer;

use Neu\Http\Message\RequestInterface;

interface InitializerInterface
{
    /**
     * Initialize the give request with a session instance.
     *
     * The returned request must return true for {@see RequestInterface::hasSession()} call.
     */
    public function initialize(RequestInterface $request): RequestInterface;
}
