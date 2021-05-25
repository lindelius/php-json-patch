<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Exception\PatchException;
use Lindelius\JsonPatch\Operation\AddOperation;
use PHPUnit\Framework\TestCase;

final class AddOperationTest extends TestCase
{
    public function testConstruct(): void
    {
        $operation = new AddOperation(5, "/a/b", ["c" => 3]);

        $this->assertSame(5, $operation->getIndex());
        $this->assertSame("/a/b", $operation->getPath());
        $this->assertSame(["c" => 3], $operation->getValue());
    }

    /**
     * Test "add" operations that should succeed.
     *
     * @dataProvider provideSuccessfulOperations
     * @param array $document
     * @param string $path
     * @param mixed $value
     * @param array $expectedResult
     * @return void
     */
    public function testSuccessfulOperations(array $document, string $path, $value, array $expectedResult): void
    {
        $this->assertSame($expectedResult, (new AddOperation(0, $path, $value))->apply($document));
    }

    public function provideSuccessfulOperations(): array
    {
        return [
            "Replace document" => [
                ["a" => 1, "b" => 2],
                "/",
                ["c" => 3],
                ["c" => 3],
            ],
            "Add root-level path" => [
                ["a" => 1],
                "/b",
                2,
                ["a" => 1, "b" => 2],
            ],
            "Add nested path" => [
                ["a" => ["b" => ["c" => 3]]],
                "/a/b/d",
                4,
                ["a" => ["b" => ["c" => 3, "d" => 4]]],
            ],
            "Add to list #1" => [
                ["a" => [1, 2, 4, 5]],
                "/a/2",
                3,
                ["a" => [1, 2, 3, 4, 5]],
            ],
            "Add to list #2" => [
                ["a" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 16, 17, 18, 19, 20]],
                "/a/14",
                15,
                ["a" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]],
            ],
            "Append to list" => [
                ["a" => [1, 2, 3, 4]],
                "/a/-",
                5,
                ["a" => [1, 2, 3, 4, 5]],
            ],
        ];
    }

    /**
     * Test "add" operations that should fail.
     *
     * @dataProvider provideErroneousOperations
     * @param array $document
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function testErroneousOperations(array $document, string $path, $value): void
    {
        $this->expectException(PatchException::class);

        (new AddOperation(0, $path, $value))->apply($document);
    }

    public function provideErroneousOperations(): array
    {
        return [
            "Invalid path #1" => [
                ["a" => 1],
                "/a/b",
                2,
            ],
            "Invalid path #2" => [
                ["a" => 1],
                "/a/b/c",
                3,
            ],
            "Non-document when replacing root" => [
                ["a" => 1],
                "/",
                "non-document value",
            ],
            "Append item to non-list" => [
                ["a" => ["b" => 2]],
                "/a/-",
                3,
            ],
            "Insert item out-of-bounds" => [
                ["a" => [0, 1, 2, 3]],
                "/a/5",
                5,
            ],
            "Add member to list" => [
                ["a" => [0, 1, 2, 3]],
                "/a/b",
                2,
            ],
        ];
    }
}
