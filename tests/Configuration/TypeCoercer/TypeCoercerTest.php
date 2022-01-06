<?php

declare(strict_types=1);

namespace Neu\Tests\Configuration\TypeCoercer;

use Neu\Configuration\Exception\InvalidEntryException;
use Neu\Configuration\TypeCoercer\TypeCoercer;
use PHPUnit\Framework\TestCase;
use Psl\Type;

final class TypeCoercerTest extends TestCase
{
    public function testThrowsForInvalidType(): void
    {
        $coercer = TypeCoercer::of(Type\string());

        $this->expectException(InvalidEntryException::class);
        $this->expectExceptionMessage('Failed to coerce entry "foo" to "string" type.');

        $coercer->coerce('foo', []);
    }
}
