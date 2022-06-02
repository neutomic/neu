<?php

declare(strict_types=1);

namespace Neu\Http\Session;

final class CacheConfiguration
{
    /**
     * This unusual past date value is taken from the php session extension source code and used "as is" for consistency.
     *
     * @link https://github.com/php/php-src/blob/e17fd1f2d95f081536cb2c02a874f286d7a82ace/ext/session/session.c#L1204
     * @link https://github.com/php/php-src/blob/e17fd1f2d95f081536cb2c02a874f286d7a82ace/ext/session/session.c#L1211
     */
    public const CACHE_PAST_DATE = 'Thu, 19 Nov 1981 08:52:00 GMT';

    public function __construct(
        public readonly int $expires,
        public readonly ?CacheLimiter $limiter = null,
    ) {
    }
}
