<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Operation\CopyOperation;
use PHPUnit\Framework\TestCase;

final class CopyOperationTest extends TestCase
{
    public function testConstruct(): void
    {
        $operation = new CopyOperation(5, "/a/b", "/a/c/b");

        $this->assertSame(5, $operation->getIndex());
        $this->assertSame("/b", $operation->getPath());
        $this->assertSame("/a/b", $operation->getFrom());
    }

    /**
     * Test "copy" operations that should succeed.
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
        $this->assertSame($expectedResult, (new CopyOperation(0, $path, $from))->apply($document));
    }

    public function provideSuccessfulOperations(): array
    {
        return [
            "Copy root-level path" => [
                ["a" => 1337, "b" => 7331],
                "/c",
                "/b",
                ["a" => 1337, "b" => 7331, "c" => 7331],
            ],
            "Copy nested path" => [
                ["a" => ["b" => ["c" => 7331]]],
                "/d",
                "/a/b/c",
                ["a" => ["b" => ["c" => 7331]], "d" => 7331],
            ],
        ];
    }

    /**
     * Test "copy" operations that should fail.
     *
     * @dataProvider provideErroneousOperations
     * @param array $document
     * @param string $path
     * @param string $from
     * @return void
     */
    public function testErroneousOperations(array $document, string $path, string $from): void
    {
        $this->expectException(FailedOperationException::class);

        (new CopyOperation(0, $path, $from))->apply($document);
    }

    public function provideErroneousOperations(): array
    {
        return [
            "Copy to root" => [
                ["/a" => 1],
                "/",
                "/a",
            ],
            "Copy from root" => [
                ["/a" => 1],
                "/b",
                "/",
            ],
            "Invalid from-path #1" => [
                ["/a" => 1],
                "/a/b",
                2,
            ],
            "Invalid from-path #2" => [
                ["/a" => 1],
                "/a/b/c",
                3,
            ],
            "Invalid from-path #3" => [
                ["/a" => [0, 1]],
                "/a/b",
                2,
            ],
        ];
    }
}
