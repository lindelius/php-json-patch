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
     * Test the protected paths functionality.
     *
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

        $patcher = new ImmutablePatcher();

        foreach ($protectedPaths as $path) {
            $patcher = $patcher->addProtectedPath($path);
        }

        $patcher->patch($document, $operations);
    }

    public function provideProtectedPaths(): array
    {
        return [
            "Protected root-level path" => [
                ["a" => 1],
                ["/a"],
                [
                    ["op" => "replace", "path" => "/a", "value" => 2],
                ],
            ],
            "Multiple protected paths" => [
                ["a" => 1, "b" => 2, "c" => 3],
                ["/a", "/b", "/c"],
                [
                    ["op" => "remove", "path" => "/b"],
                ],
            ],
            "Protected root-level path with child operation" => [
                ["a" => ["b" => ["c" => 1]]],
                ["/a"],
                [
                    ["op" => "replace", "path" => "/a/b/c", "value" => 2],
                ],
            ],
            "Protected nested path" => [
                ["a" => ["b" => ["c" => 1]]],
                ["/a/b/c"],
                [
                    ["op" => "remove", "path" => "/a/b/c"],
                ],
            ],
            "Protected nested path with parent operation" => [
                ["a" => ["b" => ["c" => 1]]],
                ["/a/b/c"],
                [
                    ["op" => "remove", "path" => "/a"],
                ],
            ],
            "Move with protected path" => [
                ["a" => 1, "b" => 2],
                ["/a"],
                [
                    ["op" => "move", "from" => "/b", "path" => "/a"],
                ],
            ],
            "Move with protected from-path" => [
                ["a" => 1, "b" => 2],
                ["/b"],
                [
                    ["op" => "move", "from" => "/b", "path" => "/a"],
                ],
            ],
        ];
    }
}
