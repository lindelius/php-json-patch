<?php

namespace Lindelius\JsonPatch\Tests\Unit\Operation;

use Lindelius\JsonPatch\Operation\CopyOperation;
use PHPUnit\Framework\TestCase;

final class CopyOperationTest extends TestCase
{
    /**
     * @dataProvider provideApply
     * @param array $document
     * @param string $path
     * @param string $from
     * @param array $expectedResult
     * @return void
     */
    public function testApply(array $document, string $path, string $from, array $expectedResult): void
    {
        $this->assertSame($expectedResult, (new CopyOperation(0, $path, $from))->apply($document));
    }

    public function provideApply(): array
    {
        return [
            "Copy Root Level Path" => [
                ["a" => 1337, "b" => 7331],
                "/c",
                "/b",
                ["a" => 1337, "b" => 7331, "c" => 7331],
            ],
            "Copy Nested Path" => [
                ["a" => ["b" => ["c" => 7331]]],
                "/d",
                "/a/b/c",
                ["a" => ["b" => ["c" => 7331]], "d" => 7331],
            ],
        ];
    }
}
