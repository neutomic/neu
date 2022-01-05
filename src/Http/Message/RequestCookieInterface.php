<?php

declare(strict_types=1);

namespace Neu\Http\Message;

use Stringable;

/**
 * A cookie as sent in a request's 'cookie' header, without any attributes.
 *
 * @link https://tools.ietf.org/html/rfc6265#section-5.4
 */
interface RequestCookieInterface extends Stringable
{
    /**
     * @return non-empty-string Name of the cookie.
     */
    public function getName(): string;

    /**
     * @param non-empty-string $name
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the cookie, and MUST return an instance that has the
     * changed name.
     */
    public function withName(string $name): static;

    /**
     * @return non-empty-string Value of the cookie.
     */
    public function getValue(): string;

    /**
     * @param non-empty-string $value
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the cookie, and MUST return an instance that has the
     * changed value.
     */
    public function withValue(string $value): static;
}
