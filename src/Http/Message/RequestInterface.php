<?php

declare(strict_types=1);

namespace Neu\Http\Message;

use InvalidArgumentException;

interface RequestInterface extends MessageInterface
{
    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return non-empty-string
     */
    public function getRequestTarget(): string;

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @param non-empty-string $requestTarget
     *
     * @return static
     */
    public function withRequestTarget(string $requestTarget): static;

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return non-empty-string Returns the request method.
     */
    public function getMethod(): string;

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param non-empty-string $method Case-sensitive method.
     *
     * @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod(string $method): static;

    /**
     * Retrieves the URI instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     */
    public function getUri(): UriInterface;

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param bool $preserveHost Preserve the original state of the Host header.
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): static;

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * @return list<array{non-empty-string, non-empty-string}>
     */
    public function getCookies(): array;

    /**
     * Return an instance with the specified cookies string pairs.
     *
     * Setting query parameters MUST NOT change the URI stored by the
     * request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * update cookies.
     *
     * @param list<array{non-empty-string, non-empty-string}> $cookies A list of cookie name, value paris.
     */
    public function withCookies(array $cookies): static;


    /**
     * Return an instance with the specified cookie name/value.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query parameters.
     *
     * @param non-empty-string $name
     * @param non-empty-string $value
     */
    public function withCookie(string $name, string $value): static;

    /**
     * Return an instance without the specified cookie.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param non-empty-string $name
     */
    public function withoutCookie(string $name): static;

    /**
     * Retrieves the deserialized query parameters, if any.
     *
     * The keys represent the query parameter name as it will be sent over the wire, and
     * each value is a list of strings associated with the query parameter.
     *
     * Note: the query parameters might not be in sync with the URI.
     *  If you need to ensure you are only getting the original
     *  values, you may need to parse the query string from `getUri()->getQuery()`.
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function getQueryParameters(): array;

    /**
     * Checks if a query parameter exists by the given case-insensitive name.
     *
     * @param non-empty-string $name Case-insensitive query parameter name.
     *
     * @return bool Returns true if any query parameter match the given name using a case-insensitive string comparison.
     *              Returns false if no matching query parameter is found in the message.
     */
    public function hasQueryParameter(string $name): bool;

    /**
     * Retrieves a request query parameter value by the given case-insensitive name.
     *
     * This method returns the query parameter value of the given case-insensitive query parameter name.
     *
     * If the query parameter does not appear in the message, this method MUST return null.
     *
     * @param non-empty-string $name Case-insensitive query parameter field name.
     *
     * @return null|string The string values as provided for the given header.
     *                     If the query parameter does not appear in the message, this method MUST return null.
     */
    public function getQueryParameter(string $name): ?string;

    /**
     * Return an instance with the specified query parameters.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, or MAY be derived
     * from some other value such as the URI.
     *
     * Setting query parameters MUST NOT change the URI stored by the
     * request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query parameters.
     *
     * @param array<non-empty-string, string> $query A dictionary of query parameters.
     */
    public function withQueryParameters(array $query): static;

    /**
     * Return an instance with the provided value replacing the specified query parameter.
     *
     * While query parameter names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getQueryParameters().
     *
     * Adding a new query string parameter MUST NOT change the URI stored by the
     * request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query parameters.
     *
     * @param non-empty-string $name
     * @param string $value
     */
    public function withQueryParameter(string $name, string $value): static;

    /**
     * Return an instance without the specified query parameter.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param non-empty-string $name
     */
    public function withoutQueryParameter(string $name): static;

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array<non-empty-string, mixed> Attributes derived from the request.
     */
    public function getAttributes(): array;

    /**
     * Checks if an attribute exists by the given name.
     *
     * @see getAttributes()
     *
     * @param non-empty-string $name The attribute name.
     */
    public function hasAttribute(string $name): bool;

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes().
     *
     * @see getAttributes()
     *
     * @param non-empty-string $name The attribute name.
     *
     * @throws InvalidArgumentException If the requested attribute is not found.
     */
    public function getAttribute(string $name): mixed;

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     *
     * @param non-empty-string $name The attribute name.
     */
    public function withAttribute(string $name, mixed $value): static;

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     *
     * @param non-empty-string $name The attribute name.
     */
    public function withoutAttribute(string $name): static;

    /**
     * Gets the body of the request.
     */
    public function getBody(): BodyInterface;

    /**
     * Return an instance with the specified message body.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the request, and MUST return a new instance that has the
     * new body stream.
     *
     * @throws InvalidArgumentException When the body is not valid.
     */
    public function withBody(BodyInterface $body): static;
}
