<?php

declare(strict_types=1);

namespace Neu\Http\Session\Persistence;

use Neu\Http\Message\RequestInterface;
use Neu\Http\Message\ResponseInterface;

interface PersistenceInterface
{
    /**
     * Persist the session data from the give request.
     *
     * Persists the session data, returning a response instance with any
     * artifacts required to return to the client.
     */
    public function persist(RequestInterface $request, ResponseInterface $response): ResponseInterface;
}
