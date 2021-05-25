<?php

namespace Lindelius\JsonPatch\Tests\Unit\Utility;

use Lindelius\JsonPatch\Utility\CompareUtility;
use PHPUnit\Framework\TestCase;

final class CompareUtilityTest extends TestCase
{
    /**
     * @dataProvider provideEquals
     * @param mixed $value
     * @param mixed $expected
     * @param bool $expectedEqual
     * @return void
     */
    public function testEquals($value, $expected, bool $expectedEqual): void
    {
        if ($expectedEqual) {
            $this->assertTrue(CompareUtility::equals($expected, $value));
        } else {
            $this->assertFalse(CompareUtility::equals($expected, $value));
        }
    }

    public function provideEquals(): array
    {
        return [
            "Equal boolean" => [
                true,
                true,
                true,
            ],
            "Non-equal boolean" => [
                true,
                false,
                false,
            ],
            "Equal number" => [
                1337,
                1337,
                true,
            ],
            "Non-equal number" => [
                1337,
                7331,
                false,
            ],
            "Non-equal number type" => [
                1337,
                "1337",
                false,
            ],
            "Equal string" => [
                "something",
                "something",
                true,
            ],
            "Non-equal string" => [
                "something",
                "something else",
                false,
            ],
            "Equal list" => [
                [0, 1, 2, 3, 4],
                [0, 1, 2, 3, 4],
                true,
            ],
            "Non-equal list #1" => [
                [0, 1, 2, 3, 4],
                [0, 1, 2, 3],
                false,
            ],
            "Non-equal list #2" => [
                [0, 1, 2, 3, 4],
                [4, 3, 2, 1, 0],
                false,
            ],
            "Equal document" => [
                ["a" => 1, "b" => 2],
                ["a" => 1, "b" => 2],
                true,
            ],
            "Equal scrambled document" => [
                ["a" => 1, "b" => 2, "c" => 3],
                ["c" => 3, "a" => 1, "b" => 2],
                true,
            ],
            "Non-equal document #1" => [
                ["a" => 1, "b" => 2],
                ["a" => 1],
                false,
            ],
            "Non-equal document #2" => [
                ["a" => 1, "b" => 2],
                ["a" => 1, "b" => 3],
                false,
            ],
            "Non-equal document #3" => [
                ["a" => 1, "b" => 2],
                ["a" => 1, "x" => 2],
                false,
            ],
            "Equal multi-level document" => [
                ["a" => 1, "b" => ["x" => [0, 1, 2], "y" => 2]],
                ["a" => 1, "b" => ["x" => [0, 1, 2], "y" => 2]],
                true,
            ],
            "Equal scrambled multi-level document" => [
                ["a" => 1, "b" => ["x" => [0, 1, 2], "y" => 2]],
                ["b" => ["y" => 2, "x" => [0, 1, 2]], "a" => 1],
                true,
            ],
            "Non-equal multi-level document #1" => [
                ["a" => 1, "b" => ["x" => 1, "y" => 2]],
                ["a" => 1, "b" => ["x" => 1, "y" => 3]],
                false,
            ],
            "Non-equal multi-level document #2" => [
                ["a" => 1, "b" => ["x" => 1, "y" => 2]],
                ["a" => 1, "b" => ["x" => 1]],
                false,
            ],
            "Non-equal multi-level document #3" => [
                ["a" => 1, "b" => ["x" => 1, "y" => 2]],
                ["a" => 1, "b" => ["x" => 1, "y" => 2, "z" => 3]],
                false,
            ],
            "Non-equal multi-level document #4" => [
                ["a" => 1, "b" => ["x" => [0, 1, 2], "y" => 2]],
                ["a" => 1, "b" => ["x" => [2, 1, 0], "y" => 2]],
                false,
            ],
        ];
    }
}
