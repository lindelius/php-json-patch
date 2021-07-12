<?php

namespace Lindelius\JsonPatch;

use Lindelius\JsonPatch\Exception\ProtectedPathException;
use Lindelius\JsonPatch\Operation\MoveOperation;
use Lindelius\JsonPatch\Operation\OperationInterface;
use Lindelius\JsonPatch\Operation\TestOperation;
use Lindelius\JsonPatch\Utility\JsonPointerUtility;
use Lindelius\JsonPatch\Utility\OperationUtility;

use function array_flip;
use function array_key_exists;
use function json_decode;
use function strpos;

final class ImmutablePatcher implements PatcherInterface
{
    private array $protectedPaths;
    private array $protectedParentPaths;

    /**
     * Construct an immutable patcher with a given set of protected paths.
     *
     * @param string[] $protectedPaths
     */
    public function __construct(array $protectedPaths = [])
    {
        $this->protectedPaths = $protectedPaths;

        // Compile all protected parent paths, and then flip the array for much
        // better look-up performance.
        $this->protectedParentPaths = array_flip(
            JsonPointerUtility::compileParentPaths($protectedPaths)
        );
    }

    public function addProtectedPath(string $path): self
    {
        return new self([...$this->protectedPaths, $path]);
    }

    public function getProtectedPaths(): array
    {
        return $this->protectedPaths;
    }

    public function patch(array $document, iterable $operations): array
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
        if (empty($this->protectedPaths)) {
            return;
        }

        // Since "test" operations are not modifying anything we should allow
        // access to protected paths, as well.
        if ($operation instanceof TestOperation) {
            return;
        }

        $sensitivePaths = $operation instanceof MoveOperation
            ? [$operation->getPath(), $operation->getFrom()]
            : [$operation->getPath()];

        foreach ($sensitivePaths as $sensitivePath) {
            // Make sure a protected path cannot be touched by modifying one of
            // its parents. In this case we are only looking for exact matches,
            // since you should be able to modify the "/a/c" path if "/a/b" is
            // protected, but not "/a" or "/".
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
