<?php

namespace Lindelius\JsonPatch\Operation;

/**
 * A common interface for JSON Patch operations.
 *
 * @internal
 */
interface OperationInterface
{
    /**
     * Apply the operation to a given document.
     *
     * @param array $document
     * @return array
     */
    public function apply(array $document): array;

    /**
     * Get the index of the operation.
     *
     * @return int
     */
    public function getIndex(): int;

    /**
     * Get the JSON Pointer path to the field that should be operated on.
     *
     * @return string
     */
    public function getPath(): string;
}
