<?php

namespace Lindelius\JsonPatch\Tests\Unit\Utility;

use Lindelius\JsonPatch\Utility\OperationUtility;
use PHPUnit\Framework\TestCase;

final class OperationUtilityTest extends TestCase
{
    /**
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
            [
                [
                    ["op" => "add", "path" => "/a", "value" => 1],
                ],
            ],
            [
                [
                    ["op" => "test", "path" => "/a/b", "value" => 1],
                    ["op" => "copy", "path" => "/c", "from" => "/a/b"],
                    ["op" => "replace", "path" => "/a/b", "value" => 2],
                ],
            ],
        ];
    }
}
