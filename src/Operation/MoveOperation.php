<?php

namespace Lindelius\JsonPatch\Operation;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Exception\InvalidOperationException;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;

use function array_is_list;
use function array_key_exists;
use function array_pop;
use function array_values;
use function is_array;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4.4
 */
final class MoveOperation implements OperationInterface
{
    private int $index;
    private string $path;
    private string $from;

    /**
     * Construct a JSON Patch "move" operation.
     *
     * @param int $index
     * @param string $path
     * @param string $from
     */
    public function __construct(int $index, string $path, string $from)
    {
        $this->index = $index;
        $this->path = $path;
        $this->from = $from;
    }

    public function apply(array $document): array
    {
        if ($this->path === "/" || $this->from === "/") {
            throw new InvalidOperationException("The path for operation {$this->index} may not target the entire document.");
        }

        $pointer = &$document;

        // Dive into the document to validate the given path
        $segments = JsonPointerUtility::parse($this->from);
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

        $valueToMove = $pointer[$lastSegment];
        unset($pointer[$lastSegment]);

        // If the value was removed from a list, then reindex it
        if ($arrayIsList) {
            $pointer = array_values($pointer);
        }

        // The remaining part should be logically identical to an add operation,
        // so why not reuse what we already have ;)
        return (new AddOperation($this->index, $this->path, $valueToMove))->apply($document);
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
     * Get the JSON Pointer path to the value that should be moved.
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }
}
