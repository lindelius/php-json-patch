<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Exception\PatchException;
use Lindelius\JsonPatch\Operation\ReplaceOperation;
use PHPUnit\Framework\TestCase;

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
     * Test "replace" operations that should succeed.
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
        $this->assertSame($expectedResult, (new ReplaceOperation(0, $path, $value))->apply($document));
    }

    public function provideSuccessfulOperations(): array
    {
        return [
            "Replace root-level path" => [
                ["a" => 1],
                "/a",
                2,
                ["a" => 2],
            ],
            "Replace nested path" => [
                ["a" => ["b" => ["c" => 3]]],
                "/a/b",
                ["c" => 4],
                ["a" => ["b" => ["c" => 4]]],
            ],
            "Replace list index" => [
                ["a" => [0, 1, 2, 3, 4]],
                "/a/3",
                0,
                ["a" => [0, 1, 2, 0, 4]],
            ],
        ];
    }

    /**
     * Test "replace" operations that should fail.
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

        (new ReplaceOperation(0, $path, $value))->apply($document);
    }

    public function provideErroneousOperations(): array
    {
        return [
            "Replace root" => [
                ["a" => 1],
                "/",
                ["b" => 2],
            ],
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
            "Replace non-existing list index" => [
                ["a" => [0, 1, 2, 3]],
                "/a/5",
                5,
            ],
        ];
    }
}
