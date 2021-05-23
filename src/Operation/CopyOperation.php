<?php

namespace Lindelius\JsonPatch\Operation;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Exception\InvalidOperationException;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4.5
 */
final class CopyOperation implements OperationInterface
{
    private int $index;
    private string $path;
    private string $from;

    /**
     * Construct a JSON Patch "copy" operation.
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

        $valueToCopy = $pointer[$lastSegment];

        // The remaining part should be logically identical to an add operation,
        // so why not reuse what we already have ;)
        return (new AddOperation($this->index, $this->path, $valueToCopy))->apply($document);
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
     * Get the JSON Pointer path to the value that should be copied.
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }
}
