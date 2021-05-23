<?php

namespace Lindelius\JsonPatch;

use Lindelius\JsonPatch\Exception\PatchException;

interface PatcherInterface
{
    /**
     * Register a new path as "protected", meaning that no patch operation that
     * modifies that value of the given path may be executed.
     *
     * @param string $path
     * @return void
     */
    public function addProtectedPath(string $path): void;

    /**
     * Get all paths that have been registered as "protected".
     *
     * @return string[]
     */
    public function getProtectedPaths(): array;

    /**
     * Patch a given document with a given set of operations.
     *
     * @param array $document The document to apply the patches to.
     * @param array $operations The JSON Patch operations in array format.
     * @return array The document after the patch operations have been applied.
     * @throws PatchException
     */
    public function patch(array $document, array $operations): array;

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
