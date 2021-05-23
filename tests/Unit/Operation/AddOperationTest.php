<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Operation\AddOperation;
use PHPUnit\Framework\TestCase;

final class AddOperationTest extends TestCase
{
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
        $this->assertSame($expectedResult, (new AddOperation(0, $path, $value))->apply($document));
    }

    public function provideApply(): array
    {
        return [
            "Replace Document" => [
                ["a" => 1, "b" => 2],
                "/",
                ["c" => 1337],
                ["c" => 1337],
            ],
            "Add Root Level Path" => [
                ["a" => 1337],
                "/b",
                7331,
                ["a" => 1337, "b" => 7331],
            ],
            "Add Nested Path" => [
                ["a" => ["b" => ["c" => 1337]]],
                "/a/b/d",
                7331,
                ["a" => ["b" => ["c" => 1337, "d" => 7331]]],
            ],
            "Add To Array #1" => [
                ["a" => [1, 2, 4, 5]],
                "/a/2",
                3,
                ["a" => [1, 2, 3, 4, 5]],
            ],
            "Add To Array #2" => [
                ["a" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 16, 17, 18, 19, 20]],
                "/a/14",
                15,
                ["a" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]],
            ],
            "Append To Array" => [
                ["a" => [1, 2, 3, 4]],
                "/a/-",
                5,
                ["a" => [1, 2, 3, 4, 5]],
            ],
        ];
    }
}
