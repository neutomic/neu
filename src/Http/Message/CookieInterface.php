<?php

declare(strict_types=1);

namespace Neu\Http\Message;

interface CookieInterface
{
    /**
     * Retrieve the value of the cookie.
     *
     * If no value is present, this method MUST return an empty string.
     */
    public function getValue(): string;

    /**
     * Retrieve the expires attribute of the cookie.
     *
     * If the attribute is not present, this method MUST return null.
     */
    public function getExpires(): ?int;

    /**
     * Retrieve the max-age attribute of the cookie.
     *
     * If the attribute is not present, this method MUST return null.
     */
    public function getMaxAge(): ?int;

    /**
     * Retrieve the path attribute of the cookie.
     *
     * If the attribute is not present, this method MUST return null.
     */
    public function getPath(): ?string;

    /**
     * Retrieve the domain attribute of the cookie.
     *
     * If the attribute is not present, this method MUST return null.
     */
    public function getDomain(): ?string;

    /**
     * Retrieve the secure attribute of the cookie.
     *
     * If the attribute is not present, this method MUST return null.
     */
    public function getSecure(): ?bool;

    /**
     * Retrieve the http-only attribute of the cookie.
     *
     * If the attribute is not present, this method MUST return null.
     */
    public function getHttpOnly(): ?bool;

    /**
     * Retrieve the same-site attribute of the cookie.
     *
     * If the attribute is not present, this method MUST return null.
     */
    public function getSameSite(): ?CookieSameSite;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified value.
     *
     * Users can provide both encoded and decoded value characters.
     * Implementations ensure the correct encoding as outlined in getValue().
     */
    public function withValue(string $value): static;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `expires` attribute value.
     *
     * A null value provided is equivalent to removing the `expires`
     * attribute.
     */
    public function withExpires(?int $expires): static;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `max-age` attribute value.
     *
     * A null value provided is equivalent to removing the `max-age`
     * attribute.
     *
     * Providing zero or negative value will make the cookie expired immediately.
     */
    public function withMaxAge(?int $maxAge): static;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `path` attribute value.
     *
     * A null value provided is equivalent to removing the `path`
     * attribute.
     */
    public function withPath(?string $path): static;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `domain` attribute value.
     *
     * A null value provided is equivalent to removing the `domain`
     * attribute.
     */
    public function withDomain(?string $domain): static;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `secure` attribute value.
     *
     * A null value provided is equivalent to removing the `secure` attribute.
     */
    public function withSecure(?bool $secure): static;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `http-only` attribute value.
     *
     * A null value provided is equivalent to removing the `http-only` attribute.
     */
    public function withHttpOnly(?bool $httpOnly): static;

    /**
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `same-site` attribute value.
     *
     * A null value provided is equivalent to removing the `same-site`
     * attribute.
     */
    public function withSameSite(?CookieSameSite $sameSite): static;
}
