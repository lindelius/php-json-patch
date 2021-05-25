<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Exception\PatchException;
use Lindelius\JsonPatch\Operation\RemoveOperation;
use PHPUnit\Framework\TestCase;

final class RemoveOperationTest extends TestCase
{
    public function testConstruct(): void
    {
        $operation = new RemoveOperation(5, "/a/b");

        $this->assertSame(5, $operation->getIndex());
        $this->assertSame("/a/b", $operation->getPath());
    }

    /**
     * Test "remove" operations that should succeed.
     *
     * @dataProvider provideSuccessfulOperations
     * @param array $document
     * @param string $path
     * @param array $expectedResult
     * @return void
     */
    public function testSuccessfulOperations(array $document, string $path, array $expectedResult): void
    {
        $this->assertSame($expectedResult, (new RemoveOperation(0, $path))->apply($document));
    }

    public function provideSuccessfulOperations(): array
    {
        return [
            "Remove root-level path" => [
                ["a" => 1, "b" => 2],
                "/b",
                ["a" => 1],
            ],
            "Remove nested path" => [
                ["a" => ["b" => ["c" => 3]]],
                "/a/b/c",
                ["a" => ["b" => []]],
            ],
            "Remove from list" => [
                ["a" => [0, 1, 2, 3, 4]],
                "/a/2",
                ["a" => [0, 1, 3, 4]],
            ],
        ];
    }

    /**
     * Test "remove" operations that should fail.
     *
     * @dataProvider provideErroneousOperations
     * @param array $document
     * @param string $path
     * @return void
     */
    public function testErroneousOperations(array $document, string $path): void
    {
        $this->expectException(PatchException::class);

        (new RemoveOperation(0, $path))->apply($document);
    }

    public function provideErroneousOperations(): array
    {
        return [
            "Remove root" => [
                ["a" => 1],
                "/",
            ],
            "Invalid path #1" => [
                ["a" => 1],
                "/a/b",
            ],
            "Invalid path #2" => [
                ["a" => 1],
                "/a/b/c",
            ],
            "Invalid path #3" => [
                ["a" => [0, 1]],
                "/a/b",
            ],
            "Remove non-existing list index" => [
                ["a" => [0, 1]],
                "/a/3",
            ],
        ];
    }
}
