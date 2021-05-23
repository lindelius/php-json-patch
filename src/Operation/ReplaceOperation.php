<?php

namespace Lindelius\JsonPatch\Operation;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Exception\InvalidOperationException;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;

use function array_key_exists;
use function is_array;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4.3
 */
final class ReplaceOperation implements OperationInterface
{
    private int $index;
    private string $path;
    private $value;

    /**
     * Construct a JSON Patch "replace" operation.
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
        if ($this->path === "/") {
            throw new InvalidOperationException("The path for operation {$this->index} may not target the entire document.");
        }

        $pointer = &$document;

        // Dive into the document to validate the given path
        foreach (JsonPointerUtility::parse($this->path) as $segment) {
            if (!is_array($pointer) || !array_key_exists($segment, $pointer)) {
                throw new FailedOperationException("The path for operation {$this->index} does not exist.");
            }

            $pointer = &$pointer[$segment];
        }

        // Completely replace the current value
        $pointer = $this->value;

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
     * Get the value that should be set for the given JSON Pointer path.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
