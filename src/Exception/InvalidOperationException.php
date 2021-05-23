<?php

namespace Lindelius\JsonPatch\Exception;

use RuntimeException;

class InvalidOperationException extends RuntimeException implements PatchException
{
    public static function missingField(int $index, string $field): self
    {
        return new self("Operation {$index} is missing required \"{$field}\" field.");
    }
}
