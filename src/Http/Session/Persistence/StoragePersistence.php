<?php

declare(strict_types=1);

namespace Neu\Http\Session\Persistence;

use Neu\Http\Message\Cookie;
use Neu\Http\Message\RequestInterface;
use Neu\Http\Message\ResponseInterface;
use Neu\Http\Session\CacheConfiguration;
use Neu\Http\Session\CacheLimiter;
use Neu\Http\Session\CookieConfiguration;
use Neu\Http\Session\Session;
use Neu\Http\Session\SessionInterface;
use Neu\Http\Session\Storage\StorageInterface;
use Psl\Env;
use Psl\Filesystem;
use Psl\Math;
use Psl\Str;

use function count;
use function date;
use function time;

final class StoragePersistence implements PersistenceInterface
{
    /**
     * Http date format.
     */
    private const HTTP_DATE_FORMAT = 'D, d M Y H:i:s T';

    public function __construct(
        private readonly StorageInterface $storage,
        private readonly CookieConfiguration $cookieConfiguration,
        private readonly CacheConfiguration $cacheConfiguration,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function persist(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$request->hasSession()) {
            return $response;
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $session = $request->getSession();
        $id = $session->getId();
        if ('' === $id && (0 === count($session->all()) || !$session->hasChanges())) {
            return $response;
        }

        if ($session->isFlushed()) {
            if ($id !== '') {
                $this->storage->flush($id);
            }

            return $response->withCookie($this->cookieConfiguration->name, new Cookie('', expires: 0));
        }

        $expires = $this->cookieConfiguration->getExpires($session);
        $id = $this->storage->write($session, $expires);

        return $this->withCacheHeaders(
            $response->withCookie($this->cookieConfiguration->name, $this->createCookie($id, $expires))
        );
    }

    protected function getPersistenceDuration(SessionInterface $session): int
    {
        $duration = $this->cookieConfiguration->lifetime;
        if ($session->has(Session::SESSION_AGE_KEY)) {
            $duration = $session->age();
        }

        return Math\maxva($duration, 0);
    }

    private function createCookie(string $id, ?int $expires): Cookie
    {
        return (new Cookie(value: $id))
            ->withExpires(($expires !== null && $expires > 0) ? $expires : null)
            ->withDomain($this->cookieConfiguration->domain)
            ->withPath($this->cookieConfiguration->path)
            ->withHttpOnly($this->cookieConfiguration->httpOnly)
            ->withSecure($this->cookieConfiguration->secure)
            ->withSameSite($this->cookieConfiguration->sameSite);
    }

    private function withCacheHeaders(ResponseInterface $response): ResponseInterface
    {
        $cacheLimiter = $this->cacheConfiguration->limiter;
        if ($cacheLimiter === null || $this->responseAlreadyHasCacheHeaders($response)) {
            return $response;
        }

        $headers = $this->generateCacheHeaders($cacheLimiter);
        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }

    private function responseAlreadyHasCacheHeaders(ResponseInterface $response): bool
    {
        return (
            $response->hasHeader('Expires') ||
            $response->hasHeader('Last-Modified') ||
            $response->hasHeader('Cache-Control') ||
            $response->hasHeader('Pragma')
        );
    }

    /**
     * @return non-empty-array<non-empty-string, non-empty-list<non-empty-string>>
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function generateCacheHeaders(CacheLimiter $limiter): array
    {
        return match ($limiter) {
            CacheLimiter::NoCache => [
                'Expires' => [CacheConfiguration::CACHE_PAST_DATE],
                'Cache-Control' => ['no-store', 'no-cache', 'must-revalidate'],
                'Pragma' => ['no-cache'],
            ],
            CacheLimiter::Public => $this->withLastModifiedAndMaxAge([
                'Expires' => [
                    date(static::HTTP_DATE_FORMAT, time() + (60 * $this->cacheConfiguration->expires)),
                ],
                'Cache-Control' => ['public'],
            ]),
            CacheLimiter::Private => $this->withLastModifiedAndMaxAge([
                'Expires' => [CacheConfiguration::CACHE_PAST_DATE],
                'Cache-Control' => ['private'],
            ]),
            CacheLimiter::PrivateNoExpire => $this->withLastModifiedAndMaxAge([
                'Cache-Control' => ['private'],
            ]),
        };
    }

    /**
     * same behavior as the PHP engine ( current_exec() is used instead of Path translator variable ).
     *
     * @link https://github.com/php/php-src/blob/e17fd1f2d95f081536cb2c02a874f286d7a82ace/ext/session/session.c#L1179-L1184
     *
     * @param non-empty-array<non-empty-string, non-empty-list<non-empty-string>> $headers
     *
     * @return non-empty-array<non-empty-string, non-empty-list<non-empty-string>>
     */
    private function withLastModifiedAndMaxAge(array $headers): array
    {
        $maxAge = 60 * $this->cacheConfiguration->expires;
        $headers['Cache-Control'][] = Str\format('max-age=%d', $maxAge);

        /**
         * @var non-empty-string $pathTranslated
         */
        $pathTranslated = Env\current_exec();

        /**
         * @var non-empty-string $lastModified
         *
         * @psalm-suppress MissingThrowsDocblock
         */
        $lastModified = date(static::HTTP_DATE_FORMAT, Filesystem\get_modification_time($pathTranslated));
        $headers['Last-Modified'][] = $lastModified;

        /** @var non-empty-array<non-empty-string, non-empty-list<non-empty-string>> */
        return $headers;
    }
}
