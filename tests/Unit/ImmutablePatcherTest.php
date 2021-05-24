<?php

namespace Lindelius\JsonPatch\Tests\Unit;

use Lindelius\JsonPatch\Exception\PatchException;
use Lindelius\JsonPatch\Exception\ProtectedPathException;
use Lindelius\JsonPatch\ImmutablePatcher;
use PHPUnit\Framework\TestCase;

/**
 * Additional tests to cover more cases than what is included in the JSON Patch
 * specification (RFC 6902).
 *
 * @see RfcTest
 */
final class ImmutablePatcherTest extends TestCase
{
    /**
     * @dataProvider provideProtectedPaths
     * @param array $document
     * @param array $protectedPaths
     * @param array $operations
     * @return void
     * @throws PatchException
     */
    public function testProtectedPaths(array $document, array $protectedPaths, array $operations): void
    {
        $this->expectException(ProtectedPathException::class);

        $patcher = new ImmutablePatcher($protectedPaths);
        $patcher->patch($document, $operations);
    }

    public function provideProtectedPaths(): array
    {
        return [
            "Protected Root Level Path" => [
                ["a" => 1],
                ["/a"],
                [
                    ["op" => "replace", "path" => "/a", "value" => 2],
                ],
            ],
            "Protected Root Level Path With Child Op" => [
                ["a" => ["b" => ["c" => 1]]],
                ["/a"],
                [
                    ["op" => "replace", "path" => "/a/b/c", "value" => 2],
                ],
            ],
            "Protected Nested Path" => [
                ["a" => ["b" => ["c" => 1]]],
                ["/a/b/c"],
                [
                    ["op" => "remove", "path" => "/a/b/c"],
                ],
            ],
            "Protected Nested Path With Parent Op" => [
                ["a" => ["b" => ["c" => 1]]],
                ["/a/b/c"],
                [
                    ["op" => "remove", "path" => "/a"],
                ],
            ],
            "Move With Protected Path" => [
                ["a" => 1, "b" => 2],
                ["/a"],
                [
                    ["op" => "move", "from" => "/b", "path" => "/a"],
                ],
            ],
            "Move With Protected From Path" => [
                ["a" => 1, "b" => 2],
                ["/b"],
                [
                    ["op" => "move", "from" => "/b", "path" => "/a"],
                ],
            ],
        ];
    }
}
