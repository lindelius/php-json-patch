<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use PHPUnit\Framework\TestCase;
use Lindelius\JsonPatch\Operation\ReplaceOperation;

final class ReplaceOperationTest extends TestCase
{
    public function testConstruct(): void
    {
        $operation = new ReplaceOperation(5, "/a/b", [1, 2, 3]);

        $this->assertSame(5, $operation->getIndex());
        $this->assertSame("/a/b", $operation->getPath());
        $this->assertSame([1, 2, 3], $operation->getValue());
    }

    /**
     * @dataProvider provideApply
     * @param array $document
     * @param string $path
     * @param mixed $value
     * @param array $expectedResult
     * @return void
     */
    public function testApply(array $document, string $path, $value, array $expectedResult): void
    {
        $this->assertSame($expectedResult, (new ReplaceOperation(0, $path, $value))->apply($document));
    }

    public function provideApply(): array
    {
        return [
            "Replace Root Level Path" => [
                ["a" => 1337],
                "/a",
                7331,
                ["a" => 7331],
            ],
            "Replace Nested Path" => [
                ["a" => ["b" => ["c" => 1337]]],
                "/a/b",
                ["c" => 7331],
                ["a" => ["b" => ["c" => 7331]]],
            ],
        ];
    }
}
