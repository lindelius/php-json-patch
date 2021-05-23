<?php

namespace Lindelius\JsonPatch\Operation;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Utility\CompareUtility;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4.6
 */
final class TestOperation implements OperationInterface
{
    private int $index;
    private string $path;
    private $value;

    /**
     * Construct a JSON Patch "test" operation.
     *
     * @param int $index
     * @param string $path
     * @param mixed $value
     */
    public function __construct(int $index, string $path, $value)
    {
        $this->index = $index;
        $this->path = $path;
        $this->value = $value;
    }

    public function apply(array $document): array
    {
        $pointer = &$document;

        // Dive into the document to validate the given path
        foreach (JsonPointerUtility::parse($this->path) as $segment) {
            if (!is_array($pointer) || !array_key_exists($segment, $pointer)) {
                throw new FailedOperationException("The path for operation {$this->index} does not exist.");
            }

            $pointer = &$pointer[$segment];
        }

        // Verify that the value matches the given expectations
        if (!CompareUtility::equals($this->value, $pointer)) {
            throw new FailedOperationException("The expected value for operation {$this->index} did not match.");
        }

        return $document;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the value to test for at the given JSON Pointer path.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
