<?php

declare(strict_types=1);

namespace Neu\Http\Message;

use InvalidArgumentException;

interface ResponseInterface extends MessageInterface
{
    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     */
    public function getStatusCode(): int;

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @throws InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus(int $code, string $reasonPhrase = ''): static;

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be an empty string.
     *
     * Implementations MAY choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase(): string;

    /**
     * Retrieve all cookies associated with the response.
     *
     * @return array<string, CookieInterface>
     */
    public function getCookies(): array;

    /**
     * Retrieves a response cookie by the given case-sensitive name.
     *
     * This method returns a cookie instance of the given
     * case-sensitive cookie name.
     *
     * If the cookie does not appear in the response, this method MUST return null.
     *
     * @param non-empty-string $name
     */
    public function getCookie(string $name): ?CookieInterface;

    /**
     * Return an instance with the provided Cookie.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed response cookies.
     *
     * @param non-empty-string $name
     *
     * @link https://tools.ietf.org/html/rfc6265#section-4.1
     */
    public function withCookie(string $name, CookieInterface $cookie): static;

    /**
     * Return an instance without the specified cookie.
     *
     * Cookie name resolution MUST be done with case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named cookie.
     *
     * @param non-empty-string $name
     */
    public function withoutCookie(string $name): static;
}
