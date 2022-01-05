<?php

declare(strict_types=1);

namespace Neu\Http\Message;

interface ResponseBodyInterface extends BodyInterface
{
    /**
     * Write all the requested data.
     */
    public function writeAll(string $bytes): void;
}
