<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use PHPUnit\Framework\TestCase;
use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Operation\TestOperation;

final class TestOperationTest extends TestCase
{
    /**
     * @dataProvider provideApply
     * @param array $document
     * @param string $path
     * @param mixed $value
     * @param bool $expectedPass
     * @return void
     */
    public function testApply(array $document, string $path, $value, bool $expectedPass): void
    {
        // The "test" operation should never actually update anything, and if
        // the value check fails it will throw an exception.
        if (!$expectedPass) {
            $this->expectException(FailedOperationException::class);
        }

        $this->assertSame($document, (new TestOperation(0, $path, $value))->apply($document));
    }

    public function provideApply(): array
    {
        return [
            "Passing" => [
                ["a" => 1337],
                "/a",
                1337,
                true,
            ],
            "Failing" => [
                ["a" => 1337],
                "/a",
                7331,
                false,
            ],
            "Passing Nested" => [
                ["a" => ["b" => ["c" => 1337]]],
                "/a/b",
                ["c" => 1337],
                true,
            ],
            "Failing Nested" => [
                ["a" => ["b" => ["c" => [1337]]]],
                "/a/b",
                ["c" => 7331],
                false,
            ],
        ];
    }
}
