<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Operation\MoveOperation;
use PHPUnit\Framework\TestCase;

final class MoveOperationTest extends TestCase
{
    public function testConstruct(): void
    {
        $operation = new MoveOperation(5, "/a/b", "/a/c/b");

        $this->assertSame(5, $operation->getIndex());
        $this->assertSame("/a/b", $operation->getPath());
        $this->assertSame("/a/c/b", $operation->getFrom());
    }

    /**
     * @dataProvider provideApply
     * @param array $document
     * @param string $path
     * @param string $from
     * @param array $expectedResult
     * @return void
     */
    public function testApply(array $document, string $path, string $from, array $expectedResult): void
    {
        $this->assertSame($expectedResult, (new MoveOperation(0, $path, $from))->apply($document));
    }

    public function provideApply(): array
    {
        return [
            "Move Root Level Path" => [
                ["a" => 1337, "b" => 7331],
                "/c",
                "/b",
                ["a" => 1337, "c" => 7331],
            ],
            "Move Nested Path" => [
                ["a" => ["b" => ["c" => 7331]]],
                "/d",
                "/a/b/c",
                ["a" => ["b" => []], "d" => 7331],
            ],
        ];
    }
}
