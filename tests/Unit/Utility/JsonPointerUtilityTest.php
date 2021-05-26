<?php

namespace Lindelius\JsonPatch\Tests\Unit\Utility;

use Lindelius\JsonPatch\Exception\InvalidPathException;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;
use PHPUnit\Framework\TestCase;

final class JsonPointerUtilityTest extends TestCase
{
    /**
     * @dataProvider provideIncorrectlyFormattedPaths
     * @param string $path
     * @return void
     */
    public function testIncorrectlyFormattedPaths(string $path): void
    {
        $this->expectException(InvalidPathException::class);

        JsonPointerUtility::parse($path);
    }

    public function provideIncorrectlyFormattedPaths(): array
    {
        return [

            "Missing prefix #1" => [""],
            "Missing prefix #2" => ["123"],
            "Missing prefix #3" => ["a/b/c"],
            "Missing prefix #4" => ["a~0b"],

            "Path with whitespace #1" => ["/a/b c"],
            "Path with whitespace #2" => ["/a/\t/c"],
            "Path with whitespace #3" => ["/a/b\n/c"],
            "Path with whitespace #4" => ["/ / "],

            "Empty segment #1" => ["//"],
            "Empty segment #2" => ["/a//b"],

        ];
    }

    /**
     * @dataProvider provideParse
     * @param string $path
     * @param string[] $expectedSegments
     * @return void
     */
    public function testParse(string $path, array $expectedSegments): void
    {
        $this->assertSame($expectedSegments, JsonPointerUtility::parse($path));
    }

    public function provideParse(): array
    {
        return [

            "Root" => [
                "/",
                [],
            ],
            "Root-level path" => [
                "/a",
                ["a"],
            ],
            "Nested path" => [
                "/a/b/c",
                ["a", "b", "c"],
            ],

            "Tilde #1" => [
                "/some~0thing/else",
                ["some~thing", "else"],
            ],
            "Tilde #2" => [
                "/some~00thing/else",
                ["some~0thing", "else"],
            ],
            "Tilde #3" => [
                "/some~01thing/else",
                ["some~1thing", "else"],
            ],

            "Forward slash #1" => [
                "/some~1thing/else",
                ["some/thing", "else"],
            ],
            "Forward slash #2" => [
                "/some~10thing/else",
                ["some/0thing", "else"],
            ],
            "Forward slash #3" => [
                "/some~11thing/else",
                ["some/1thing", "else"],
            ],

            "Specials combined" => [
                "/some~0~1thing/else",
                ["some~/thing", "else"],
            ],
            "Specials combined reverse" => [
                "/some~1~0thing/else",
                ["some/~thing", "else"],
            ],

        ];
    }

    /**
     * @dataProvider provideCompileParentPaths
     * @param string[] $paths
     * @param string[] $expected
     * @return void
     */
    public function testCompileParentPaths(array $paths, array $expected): void
    {
        $compiled = JsonPointerUtility::compileParentPaths($paths);
        sort($compiled);

        $this->assertSame($expected, $compiled);
    }

    public function provideCompileParentPaths(): array
    {
        return [
            "Root includes root" => [
                ["/"],
                ["/"],
            ],
            "Root-level path includes root" => [
                ["/a"],
                ["/"],
            ],
            "Nested path includes all parents" => [
                ["/a/b/c"],
                ["/", "/a", "/a/b"],
            ],
            "Encoded path includes all parents" => [
                ["/a/a~0a/a~0b~1c/1"],
                ["/", "/a", "/a/a~0a", "/a/a~0a/a~0b~1c"],
            ],
        ];
    }
}
