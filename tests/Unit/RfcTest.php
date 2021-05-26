<?php

namespace Lindelius\JsonPatch\Tests\Unit;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Exception\PatchException;
use Lindelius\JsonPatch\ImmutablePatcher;
use PHPUnit\Framework\TestCase;

/**
 * The tests included with the JSON Patch specification (RFC 6902).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6902#appendix-A
 */
final class RfcTest extends TestCase
{
    /**
     * Test all patch operations from the RFC that should succeed.
     *
     * @dataProvider provideSuccessfulPatches
     * @param array $document
     * @param array $operations
     * @param array $expected
     * @return void
     * @throws PatchException
     */
    public function testSuccessfulPatches(array $document, array $operations, array $expected): void
    {
        $this->assertSame($expected, (new ImmutablePatcher())->patch($document, $operations));
    }

    /**
     * @dataProvider provideSuccessfulPatches
     * @param array $document
     * @param array $operations
     * @param array $expected
     * @return void
     * @throws PatchException
     */
    public function testSuccessfulPatchesFromJson(array $document, array $operations, array $expected): void
    {
        $this->assertSame($expected, (new ImmutablePatcher())->patchFromJson($document, json_encode($operations)));
    }

    public function provideSuccessfulPatches(): array
    {
        return [
            "A.1" => [
                ["foo" => "bar"],
                [
                    ["op" => "add", "path" => "/baz", "value" => "qux"],
                ],
                ["foo" => "bar", "baz" => "qux"],
            ],
            "A.2" => [
                ["foo" => ["bar", "baz"]],
                [
                    ["op" => "add", "path" => "/foo/1", "value" => "qux"],
                ],
                ["foo" => ["bar", "qux", "baz"]],
            ],
            "A.3" => [
                ["baz" => "qux", "foo" => "bar"],
                [
                    ["op" => "remove", "path" => "/baz"],
                ],
                ["foo" => "bar"],
            ],
            "A.4" => [
                ["foo" => ["bar", "qux", "baz"]],
                [
                    ["op" => "remove", "path" => "/foo/1"],
                ],
                ["foo" => ["bar", "baz"]],
            ],
            "A.5" => [
                ["baz" => "qux", "foo" => "bar"],
                [
                    ["op" => "replace", "path" => "/baz", "value" => "boo"],
                ],
                ["baz" => "boo", "foo" => "bar"],
            ],
            "A.6" => [
                ["foo" => ["bar" => "baz", "waldo" => "fred"], "qux" => ["corge" => "grault"]],
                [
                    ["op" => "move", "from" => "/foo/waldo", "path" => "/qux/thud"],
                ],
                ["foo" => ["bar" => "baz"], "qux" => ["corge" => "grault", "thud" => "fred"]],
            ],
            "A.7" => [
                ["foo" => ["all", "grass", "cows", "eat"]],
                [
                    ["op" => "move", "from" => "/foo/1", "path" => "/foo/3"],
                ],
                ["foo" => ["all", "cows", "eat", "grass"]],
            ],
            "A.8" => [
                ["baz" => "qux", "foo" => ["a", 2, "c"]],
                [
                    ["op" => "test", "path" => "/baz", "value" => "qux"],
                    ["op" => "test", "path" => "/foo/1", "value" => 2],
                ],
                ["baz" => "qux", "foo" => ["a", 2, "c"]],
            ],
            "A.10" => [
                ["foo" => "bar"],
                [
                    ["op" => "add", "path" => "/child", "value" => ["grandchild" => []]],
                ],
                ["foo" => "bar", "child" => ["grandchild" => []]],
            ],
            "A.11" => [
                ["foo" => "bar"],
                [
                    ["op" => "add", "path" => "/baz", "value" => "qux", "xyz" => 123],
                ],
                ["foo" => "bar", "baz" => "qux"],
            ],
            "A.14" => [
                ["/" => 9, "~1" => 10],
                [
                    ["op" => "test", "path" => "/~01", "value" => 10],
                ],
                ["/" => 9, "~1" => 10],
            ],
            "A.16" => [
                ["foo" => ["bar"]],
                [
                    ["op" => "add", "path" => "/foo/-", "value" => ["abc", "def"]],
                ],
                ["foo" => ["bar", ["abc", "def"]]],
            ],
        ];
    }

    /**
     * Test all patch operations from the RFC that should fail.
     *
     * @dataProvider provideErroneousPatches
     * @param array $document
     * @param array $operations
     * @return void
     * @throws PatchException
     */
    public function testErroneousPatches(array $document, array $operations): void
    {
        $this->expectException(FailedOperationException::class);

        (new ImmutablePatcher())->patch($document, $operations);
    }

    /**
     * @dataProvider provideErroneousPatches
     * @param array $document
     * @param array $operations
     * @return void
     * @throws PatchException
     */
    public function testErroneousPatchesFromJson(array $document, array $operations): void
    {
        $this->expectException(FailedOperationException::class);

        (new ImmutablePatcher())->patchFromJson($document, json_encode($operations));
    }

    public function provideErroneousPatches(): array
    {
        return [
            "A.9" => [
                ["baz" => "qux"],
                [
                    ["op" => "test", "path" => "/baz", "value" => "bar"],
                ],
            ],
            "A.12" => [
                ["foo" => "bar"],
                [
                    ["op" => "add", "path" => "/baz/bat", "value" => "qux"],
                ],
            ],

            // The "A.13" test does not apply as PHP automatically throws away
            // duplicate members when parsing any JSON input.

            "A.15" => [
                ["/" => 9, "~1" => 10],
                [
                    ["op" => "test", "path" => "/~01", "value" => "10"],
                ],
            ],
        ];
    }
}
