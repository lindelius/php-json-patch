<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use PHPUnit\Framework\TestCase;
use Lindelius\JsonPatch\Operation\RemoveOperation;

final class RemoveOperationTest extends TestCase
{
    public function testConstruct(): void
    {
        $operation = new RemoveOperation(5, "/a/b");

        $this->assertSame(5, $operation->getIndex());
        $this->assertSame("/a/b", $operation->getPath());
    }

    /**
     * @dataProvider provideApply
     * @param array $document
     * @param string $path
     * @param array $expectedResult
     * @return void
     */
    public function testApply(array $document, string $path, array $expectedResult): void
    {
        $this->assertSame($expectedResult, (new RemoveOperation(0, $path))->apply($document));
    }

    public function provideApply(): array
    {
        return [
            "Remove Root Level Path" => [
                ["a" => 1337, "b" => 7331],
                "/b",
                ["a" => 1337],
            ],
            "Remove Nested Path" => [
                ["a" => ["b" => ["c" => 7331]]],
                "/a/b/c",
                ["a" => ["b" => []]],
            ],
            "Remove From Array" => [
                ["a" => [1, 2, 3, 4, 5]],
                "/a/2",
                ["a" => [1, 2, 4, 5]],
            ],
        ];
    }
}
