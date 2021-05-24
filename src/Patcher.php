<?php

namespace Lindelius\JsonPatch;

use Lindelius\JsonPatch\Exception\ProtectedPathException;
use Lindelius\JsonPatch\Operation\MoveOperation;
use Lindelius\JsonPatch\Operation\OperationInterface;
use Lindelius\JsonPatch\Operation\TestOperation;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;
use Lindelius\JsonPatch\Utility\OperationUtility;

use function array_key_exists;
use function json_decode;
use function strpos;

final class Patcher implements PatcherInterface
{
    private array $protectedPaths = [];
    private array $protectedParentPaths = [];

    public function addProtectedPath(string $path): void
    {
        $this->protectedPaths[] = $path;

        // Compile all parent paths that should also be protected
        $segments = JsonPointerUtility::parse($path);
        $segmentPrefix = "";

        foreach ($segments as $segment) {
            $segment = $segmentPrefix . "/" . $segment;
            $segmentPrefix = $segment;

            $this->protectedParentPaths[$segment] = $segment;
        }
    }

    public function getProtectedPaths(): array
    {
        return $this->protectedPaths;
    }

    public function patch(array $document, array $operations): array
    {
        // Apply the operations in order. If one fails, all should fail.
        // https://datatracker.ietf.org/doc/html/rfc6902#section-5
        foreach (OperationUtility::parse($operations) as $operation) {
            $this->ensurePathNotProtected($operation);

            $document = $operation->apply($document);
        }

        return $document;
    }

    public function patchFromJson(array $document, string $json): array
    {
        return $this->patch($document, json_decode($json, true));
    }

    /**
     * Ensure that the path for a given operation is not protected.
     *
     * @param OperationInterface $operation
     * @return void
     * @throws ProtectedPathException
     */
    private function ensurePathNotProtected(OperationInterface $operation): void
    {
        if (empty($this->protectedPaths) || $operation instanceof TestOperation) {
            return;
        }

        $sensitivePaths = $operation instanceof MoveOperation
            ? [$operation->getPath(), $operation->getFrom()]
            : [$operation->getPath()];

        foreach ($sensitivePaths as $sensitivePath) {
            // Make sure a protected path cannot be touched by modifying one of
            // its parents. In this case we are only looking for exact matches,
            // since you should be able to modify the "/a/c" path if "/a/b" is
            // protected, but not "/a".
            if (array_key_exists($sensitivePath, $this->protectedParentPaths)) {
                throw new ProtectedPathException("The path for operation {$operation->getIndex()} is protected.");
            }

            // Make sure neither the protected path nor any of its child paths
            // can be modified directly. If the "/a/b" path is protected, you
            // should not be able to modify "/a/b/c".
            foreach ($this->protectedPaths as $path) {
                if (strpos($sensitivePath . "/", $path . "/") === 0) {
                    throw new ProtectedPathException("The path for operation {$operation->getIndex()} is protected.");
                }
            }
        }
    }
}
