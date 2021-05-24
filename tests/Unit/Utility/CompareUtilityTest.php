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
     * @param bool $expectedResult
     * @return void
     */
    public function testEquals($value, $expected, bool $expectedResult): void
    {
        if ($expectedResult) {
            $this->assertTrue(CompareUtility::equals($expected, $value));
        } else {
            $this->assertFalse(CompareUtility::equals($expected, $value));
        }
    }

    public function provideEquals(): array
    {
        return [
            "Boolean" => [
                true,
                true,
                true,
            ],
            "Boolean fail" => [
                true,
                false,
                false,
            ],
        ];
    }
}
