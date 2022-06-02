<?php

declare(strict_types=1);

namespace Neu\Http\Session;

use Neu\Http\Message\CookieSameSite;

final class CookieConfiguration
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public readonly string $name,
        public readonly int $lifetime,
        public readonly string $path,
        public readonly string $domain,
        public readonly bool $secure,
        public readonly bool $httpOnly,
        public readonly CookieSameSite $sameSite,
    ) {
    }

    /**
     * @return null|int<1, max>
     */
    public function getExpires(SessionInterface $session): null|int
    {
        $duration = $this->lifetime;
        if ($session->has(Session::SESSION_AGE_KEY)) {
            $duration = $session->age();
        }

        if ($duration <= 0) {
            return null;
        }

        return $duration;
    }
}
