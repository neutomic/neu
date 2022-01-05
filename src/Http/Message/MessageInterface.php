<?php

declare(strict_types=1);

namespace Neu\Http\Message;

use InvalidArgumentException;

interface MessageInterface
{
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return non-empty-string HTTP protocol version.
     */
    public function getProtocolVersion(): string;

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param non-empty-string $version HTTP protocol version
     */
    public function withProtocolVersion(string $version): static;

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is a list of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         $header = $name . ": " . Str\join($values, ', ');
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array<non-empty-string, non-empty-list<non-empty-string>> Returns a dictionary of the message's headers.
     *  Each key MUST be a header name, and each value MUST be a list of strings for that header.
     */
    public function getHeaders(): array;

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param non-empty-string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header name using a case-insensitive string comparison.
     *  Returns false if no matching header name is found in the message.
     */
    public function hasHeader(string $name): bool;

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns a list of all the header values of the given case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return null.
     *
     * @param non-empty-string $name Case-insensitive header field name.
     *
     * @return null|non-empty-list<non-empty-string> An array of string values as provided for the given header.
     *  If the header does not appear in the message, this method MUST return null.
     */
    public function getHeader(string $name): ?array;

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return null.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return null|non-empty-string A string of values as provided for the given header concatenated together using a comma.
     *  If the header does not appear in the message, this method MUST return null.
     */
    public function getHeaderLine(string $name): ?string;

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param non-empty-string $name Case-insensitive header field name.
     * @param non-empty-string|non-empty-list<non-empty-string> $value Header value(s).
     *
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withHeader(string $name, string|array $value): static;

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param non-empty-string $name Case-insensitive header field name to add.
     * @param non-empty-string|non-empty-list<non-empty-string> $value Header value(s).
     *
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader(string $name, string|array $value): static;

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param non-empty-string $name Case-insensitive header field name to remove.
     */
    public function withoutHeader(string $name): static;
}
