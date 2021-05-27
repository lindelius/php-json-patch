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
     * @param string[] $protectedPaths
     * @param array[] $operations
     * @return void
     * @throws PatchException
     */
    public function testProtectedPaths(array $document, array $protectedPaths, array $operations): void
    {
        $patcher = new ImmutablePatcher();

        foreach ($protectedPaths as $path) {
            $patcher = $patcher->addProtectedPath($path);
        }

        // First, verify that all paths were added
        $this->assertSame($protectedPaths, $patcher->getProtectedPaths());

        // Then, verify that they will indeed block the operation(s)
        $this->expectException(ProtectedPathException::class);
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
            "Multiple protected nested paths" => [
                ["a" => ["b" => ["c" => 3]]],
                ["/a", "/a/b", "/a/b/c"],
                [
                    ["op" => "remove", "path" => "/a/b"],
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

    /**
     * Test that other operations can still be executed successfully when using
     * the protected paths functionality.
     *
     * @dataProvider provideNoUnwantedPathProtection
     * @param array $document
     * @param string[] $protectedPaths
     * @param array[] $operations
     * @param array $expected
     * @return void
     * @throws PatchException
     */
    public function testNoUnwantedPathProtection(array $document, array $protectedPaths, array $operations, array $expected): void
    {
        $patcher = new ImmutablePatcher();

        foreach ($protectedPaths as $path) {
            $patcher = $patcher->addProtectedPath($path);
        }

        $this->assertSame($protectedPaths, $patcher->getProtectedPaths());
        $this->assertSame($expected, $patcher->patch($document, $operations));
    }

    public function provideNoUnwantedPathProtection(): array
    {
        return [
            "No diagonal path protection" => [
                ["a" => ["b" => ["c" => 3]]],
                ["/a/b/c"],
                [
                    ["op" => "add", "path" => "/a/x", "value" => 1],
                ],
                ["a" => ["b" => ["c" => 3], "x" => 1]],
            ],
            "No partial matching on sibling paths" => [
                ["a" => ["b" => 2, "b-2" => 2]],
                ["/a/b"],
                [
                    ["op" => "replace", "path" => "/a/b-2", "value" => 3],
                ],
                ["a" => ["b" => 2, "b-2" => 3]],
            ],
        ];
    }
}
