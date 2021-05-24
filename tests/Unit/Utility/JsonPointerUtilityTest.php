<?php

namespace Lindelius\JsonPatch\Tests\Unit\Utility;

use Lindelius\JsonPatch\Exception\InvalidPathException;
use PHPUnit\Framework\TestCase;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;

final class JsonPointerUtilityTest extends TestCase
{
    /**
     * @dataProvider provideRequirePrefix
     * @param string $path
     * @return void
     */
    public function testRequirePrefix(string $path): void
    {
        $this->expectException(InvalidPathException::class);

        JsonPointerUtility::parse($path);
    }

    public function provideRequirePrefix(): array
    {
        return [
            [""],
            ["a"],
            ["a/b/c"],
            ["123"],
            ["a/1"],
            ["a~0b"],
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
            "Root Level Path" => [
                "/a",
                ["a"],
            ],
            "Nested Path" => [
                "/a/b/c",
                ["a", "b", "c"],
            ],

            // Tilde symbols and forward slashes must be encoded as ~0 and ~1, respectively.
            // https://datatracker.ietf.org/doc/html/rfc6901#section-3
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
            "Forward Slash #1" => [
                "/some~1thing/else",
                ["some/thing", "else"],
            ],
            "Forward Slash #2" => [
                "/some~10thing/else",
                ["some/0thing", "else"],
            ],
            "Forward Slash #3" => [
                "/some~11thing/else",
                ["some/1thing", "else"],
            ],
            "Specials Combined" => [
                "/some~0~1thing/else",
                ["some~/thing", "else"],
            ],
            "Specials Combined Reverse" => [
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
            [
                ["/"],
                ["/"],
            ],
            [
                ["/a"],
                ["/"],
            ],
            [
                ["/a/b/c"],
                ["/", "/a", "/a/b"],
            ],
            [
                ["/a/a~0a/a~0b~1c/1"],
                ["/", "/a", "/a/a~0a", "/a/a~0a/a~0b~1c"],
            ],
        ];
    }
}
