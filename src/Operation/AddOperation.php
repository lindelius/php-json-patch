<?php

namespace Lindelius\JsonPatch\Operation;

use Lindelius\JsonPatch\Exception\FailedOperationException;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;

use function array_is_list;
use function array_key_exists;
use function array_pop;
use function array_splice;
use function count;
use function is_array;
use function preg_match;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4.1
 */
final class AddOperation implements OperationInterface
{
    private const END_OF_ARRAY_TOKEN = "-";

    private int $index;
    private string $path;
    private $value;

    /**
     * Construct a JSON Patch "add" operation.
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
            if (is_array($this->value) && !array_is_list($this->value)) {
                return $this->value;
            } else {
                throw new FailedOperationException("The root document may only be replaced by another document.");
            }
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

        if (!is_array($pointer)) {
            throw new FailedOperationException("The path for operation {$this->index} does not exist.");
        }

        // Check if we should append the value to the end of a list
        if ($lastSegment === self::END_OF_ARRAY_TOKEN) {
            if (array_is_list($pointer)) {
                $pointer[] = $this->value;

                return $document;
            } else {
                throw new FailedOperationException("The path for operation {$this->index} does not reference a list.");
            }
        }

        $currentCount = count($pointer);

        // We can only know for sure that the referenced array is a list if
        // there is at least one item in it. If not, treat it as a JSON object.
        if (array_is_list($pointer) && $currentCount > 0) {
            // We expect a numeric index when dealing with lists
            if (preg_match("/[0-9]+/", $lastSegment)) {
                $lastSegment = (int) $lastSegment;

                // The index may reference a "new" item at the end of the array,
                // but not further than that.
                if ($lastSegment > $currentCount) {
                    throw new FailedOperationException("The path for operation {$this->index} is out of bounds.");
                }

                // Insert the value at the given index without replacing any
                // of the existing items. Only the "replace" operation may
                // replace the value at a given list index.
                if ($lastSegment === $currentCount) {
                    $pointer[] = $this->value;
                } else {
                    array_splice($pointer, $lastSegment, 0, $this->value);
                }
            } else {
                throw new FailedOperationException("The path for operation {$this->index} does not reference an object.");
            }
        } else {
            // Set the value at the given path, whether or not it already exists
            $pointer[$lastSegment] = $this->value;
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
     * Get the value that should be set for the given JSON Pointer path.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function jsonSerialize(): array
    {
        return [
            "op" => "add",
            "path" => $this->path,
            "value" => $this->value,
        ];
    }
}
