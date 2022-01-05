<?php

declare(strict_types=1);

namespace Neu\Http\Message;

interface BodyInterface
{
    /**
     * Read from the body, waiting for data if necessary.
     *
     * This method must be implemented in such as way that it wait for previous read
     * operations to finish before attempting to read from the body.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.s
     *
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @return string the read data on success, or an empty string if the end of file is reached, or the body has been closed.
     */
    public function read(?int $max_bytes = null): string;

    /**
     * Read until there is no more data to read.
     *
     * This method must be implemented in such as way that it wait for previous read
     * operations to finish before attempting to read from the body.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     *
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @return string the read data on success, or an empty string if the end of file is reached, or the body has been closed.
     */
    public function readAll(?int $max_bytes = null): string;
}
