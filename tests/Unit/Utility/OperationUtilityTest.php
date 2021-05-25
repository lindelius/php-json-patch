<?php

namespace Lindelius\JsonPatch\Tests\Unit\Utility;

use Lindelius\JsonPatch\Exception\InvalidOperationException;
use Lindelius\JsonPatch\Utility\OperationUtility;
use PHPUnit\Framework\TestCase;

final class OperationUtilityTest extends TestCase
{
    /**
     * Test operations that should be parsed successfully.
     *
     * @dataProvider provideParse
     * @param array[] $operations
     * @return void
     */
    public function testParse(array $operations): void
    {
        $serialized = [];

        foreach (OperationUtility::parse($operations) as $operation) {
            $serialized[] = $operation->jsonSerialize();
        }

        $this->assertSame($operations, $serialized);
    }

    public function provideParse(): array
    {
        return [
            "Single operation" => [
                [
                    ["op" => "add", "path" => "/a", "value" => 1],
                ],
            ],
            "Multiple operations" => [
                [
                    ["op" => "add", "path" => "/a", "value" => 1],
                    ["op" => "test", "path" => "/a", "value" => 1],
                    ["op" => "copy", "path" => "/b", "from" => "/a"],
                    ["op" => "test", "path" => "/b", "value" => 1],
                ],
            ],
            "Every operation" => [
                [
                    ["op" => "add", "path" => "/a", "value" => 1],
                    ["op" => "copy", "path" => "/b", "from" => "/a"],
                    ["op" => "move", "path" => "/x", "from" => "/b"],
                    ["op" => "remove", "path" => "/x"],
                    ["op" => "replace", "path" => "/a", "value" => 2],
                    ["op" => "test", "path" => "/a", "value" => 2],
                ],
            ],
        ];
    }

    /**
     * Test operations that should error when parsed.
     *
     * @dataProvider provideParseErrors
     * @param array $operations
     * @return void
     */
    public function testParseErrors(array $operations): void
    {
        $this->expectException(InvalidOperationException::class);

        // Since the parsing is implemented as a generator, we need to actually
        // iterate over it in order to test it.
        iterator_to_array(OperationUtility::parse($operations));
    }

    public function provideParseErrors(): array
    {
        return [
            "Incorrectly indexed operations" => [
                [
                    0 => ["op" => "add", "path" => "/a", "value" => 1],
                    2 => ["op" => "add", "path" => "/b", "value" => 2],
                    3 => ["op" => "add", "path" => "/c", "value" => 3],
                ],
            ],
            "Invalid data" => [
                [
                    "something else",
                ],
            ],
            "Missing op field" => [
                [
                    ["path" => "/a", "value" => 1],
                ],
            ],
            "Missing path field" => [
                [
                    ["op" => "add", "value" => 1],
                ],
            ],
            "Unsupported operation" => [
                [
                    ["op" => "add", "path" => "/a", "value" => 1],
                    ["op" => "delete", "path" => "/a"],
                ],
            ],
            "Add operation without value" => [
                [
                    ["op" => "add", "path" => "/a"],
                ],
            ],
            "Copy operation without from-path" => [
                [
                    ["op" => "copy", "path" => "/a"],
                ],
            ],
            "Move operation without from-path" => [
                [
                    ["op" => "move", "path" => "/a"],
                ],
            ],
            "Replace operation without value" => [
                [
                    ["op" => "replace", "path" => "/a"],
                ],
            ],
            "Test operation without value" => [
                [
                    ["op" => "test", "path" => "/a"],
                ],
            ],
        ];
    }
}
