<?php

namespace Lindelius\JsonPatch;

use Lindelius\JsonPatch\Exception\PatchException;

interface PatcherInterface
{
    /**
     * Register a given path as "protected", meaning that no patch operation
     * that modifies the value of the given path may be executed.
     *
     * @param string $path The JSON Pointer path that should be protected.
     * @return self
     */
    public function addProtectedPath(string $path): self;

    /**
     * Get all paths that have been registered as "protected".
     *
     * @return string[] The JSON Pointer paths that are protected.
     */
    public function getProtectedPaths(): array;

    /**
     * Patch a given document with a given set of operations.
     *
     * @param array $document The document to apply the patches to.
     * @param iterable $operations The JSON Patch operations in array format.
     * @return array The document after the patch operations have been applied.
     * @throws PatchException
     */
    public function patch(array $document, iterable $operations): array;

    /**
     * Patch a given document with a given set of operations.
     *
     * @param array $document The document to apply the patches to.
     * @param string $json The JSON Patch operations as a JSON string.
     * @return array The document after the patch operations have been applied.
     * @throws PatchException
     */
    public function patchFromJson(array $document, string $json): array;
}
