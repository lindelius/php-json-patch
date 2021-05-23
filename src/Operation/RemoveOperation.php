<?php

namespace Lindelius\JsonPatch\Operation;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Exception\InvalidOperationException;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4.2
 */
final class RemoveOperation implements OperationInterface
{
    private int $index;
    private string $path;

    /**
     * Construct a JSON Patch "remove" operation.
     *
     * @param int $index
     * @param string $path
     */
    public function __construct(int $index, string $path)
    {
        $this->index = $index;
        $this->path = $path;
    }

    public function apply(array $document): array
    {
        if ($this->path === "/") {
            throw new InvalidOperationException("The path for operation {$this->index} may not target the entire document.");
        }

        $pointer = &$document;

        // Dive into the document to validate the given path
        $segments = JsonPointerUtility::parse($this->path);
        $lastSegment = array_pop($segments);

        foreach ($segments as $segment) {
            if (!is_array($pointer) || !array_key_exists($segment, $pointer)) {
                throw new FailedOperationException("The path for operation {$this->index} does not exist.");
            }

            $pointer = &$pointer[$segment];
        }

        if (!is_array($pointer) || !array_key_exists($lastSegment, $pointer)) {
            throw new FailedOperationException("The path for operation {$this->index} does not exist.");
        }

        $arrayIsList = array_is_list($pointer);
        unset($pointer[$lastSegment]);

        // If the value was removed from a list, then reindex it
        if ($arrayIsList) {
            $pointer = array_values($pointer);
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
}
