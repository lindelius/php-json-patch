<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Exception\PatchException;
use Lindelius\JsonPatch\Operation\TestOperation;
use PHPUnit\Framework\TestCase;

final class TestOperationTest extends TestCase
{
    public function testConstruct(): void
    {
        $operation = new TestOperation(5, "/a/b", "some-value");

        $this->assertSame(5, $operation->getIndex());
        $this->assertSame("/a/b", $operation->getPath());
        $this->assertSame("some-value", $operation->getValue());
    }

    /**
     * Test "test" operations that should succeed.
     *
     * @dataProvider provideSuccessfulOperations
     * @param array $document
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function testSuccessfulOperations(array $document, string $path, $value): void
    {
        // The "test" operation should never actually update anything, and if
        // the value check fails it will throw an exception.
        $this->assertSame($document, (new TestOperation(0, $path, $value))->apply($document));
    }

    public function provideSuccessfulOperations(): array
    {
        return [
            "Test root-level path" => [
                ["a" => 1],
                "/a",
                1,
            ],
            "Test nested path" => [
                ["a" => ["b" => ["c" => 3]]],
                "/a/b",
                ["c" => 3],
            ],
            "Test list item" => [
                ["a" => ["b" => [0, 1, 2, 3, 4]]],
                "/a/b/3",
                3,
            ],
            "Test encoded path" => [
                ["a" => 1, "~b" => 2, "c" => 3],
                "/~0b",
                2,
            ],
        ];
    }

    /**
     * Test "test" operations that should fail.
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

        (new TestOperation(0, $path, $value))->apply($document);
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
            "Invalid path #3" => [
                ["a" => [0, 1]],
                "/a/b",
                2,
            ],
            "Failed root-level test #1" => [
                ["a" => 1],
                "/a",
                "1",
            ],
            "Failed root-level test #2" => [
                ["a" => 1],
                "/a",
                [1],
            ],
            "Failed nested test" => [
                ["a" => ["b" => ["c" => 3]]],
                "/a/b",
                ["d" => 4],
            ],
        ];
    }
}
