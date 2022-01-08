<?php

declare(strict_types=1);

namespace Neu\Http\Session\Initializer;

use Neu\Http\Message\RequestInterface;
use Neu\Http\Session\CookieConfiguration;
use Neu\Http\Session\Session;
use Neu\Http\Session\Storage\StorageInterface;

final class StorageInitializer implements InitializerInterface
{
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly CookieConfiguration $sessionCookieConfiguration,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function initialize(RequestInterface $request): RequestInterface
    {
        $cookies = $request->getCookies();
        foreach ($cookies as [$name, $value]) {
            if ($name === $this->sessionCookieConfiguration->name) {
                $session = $this->storage->read($value);

                return $request->withSession($session);
            }
        }

        return $request->withSession(new Session([]));
    }
}
