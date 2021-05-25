<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Exception\PatchException;
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
     * Test "move" operations that should succeed.
     *
     * @dataProvider provideSuccessfulOperations
     * @param array $document
     * @param string $path
     * @param string $from
     * @param array $expectedResult
     * @return void
     */
    public function testSuccessfulOperations(array $document, string $path, string $from, array $expectedResult): void
    {
        $this->assertSame($expectedResult, (new MoveOperation(0, $path, $from))->apply($document));
    }

    public function provideSuccessfulOperations(): array
    {
        return [
            "Move root-level path" => [
                ["a" => 1, "b" => 2],
                "/c",
                "/b",
                ["a" => 1, "c" => 2],
            ],
            "Move nested path" => [
                ["a" => ["b" => ["c" => 3]]],
                "/d",
                "/a/b/c",
                ["a" => ["b" => []], "d" => 3],
            ],
            "Move list index #1" => [
                ["a" => [0, 1, 2, 3, 4]],
                "/b",
                "/a/3",
                ["a" => [0, 1, 2, 4], "b" => 3],
            ],
            "Move list index #2" => [
                ["a" => [0, 1, 2, 3, 4]],
                "/a/1",
                "/a/3",
                ["a" => [0, 3, 1, 2, 4]],
            ],
        ];
    }

    /**
     * Test "move" operations that should fail.
     *
     * @dataProvider provideErroneousOperations
     * @param array $document
     * @param string $path
     * @param string $from
     * @return void
     */
    public function testErroneousOperations(array $document, string $path, string $from): void
    {
        $this->expectException(PatchException::class);

        (new MoveOperation(0, $path, $from))->apply($document);
    }

    public function provideErroneousOperations(): array
    {
        return [
            "Move from root" => [
                ["a" => 1],
                "/anywhere",
                "/",
            ],
            "Move to root" => [
                ["a" => 1, "anywhere" => true],
                "/",
                "/anywhere",
            ],
            "Invalid from-path #1" => [
                ["a" => 1],
                "/x",
                "/a/b",
            ],
            "Invalid from-path #2" => [
                ["a" => 1],
                "/x",
                "/a/b/c",
            ],
            "Invalid from-path #3" => [
                ["a" => [0, 1]],
                "/x",
                "/a/b",
            ],
        ];
    }
}
