<?php

namespace Lindelius\JsonPatch\Utility;

use Lindelius\JsonPatch\Exception\InvalidOperationException;
use Lindelius\JsonPatch\Operation\AddOperation;
use Lindelius\JsonPatch\Operation\CopyOperation;
use Lindelius\JsonPatch\Operation\MoveOperation;
use Lindelius\JsonPatch\Operation\OperationInterface;
use Lindelius\JsonPatch\Operation\RemoveOperation;
use Lindelius\JsonPatch\Operation\ReplaceOperation;
use Lindelius\JsonPatch\Operation\TestOperation;

use function array_key_exists;
use function is_array;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4
 */
final class OperationUtility
{
    private const ADD_OPERATION = "add";
    private const COPY_OPERATION = "copy";
    private const MOVE_OPERATION = "move";
    private const REMOVE_OPERATION = "remove";
    private const REPLACE_OPERATION = "replace";
    private const TEST_OPERATION = "test";

    /**
     * Parse a given set of JSON Patch operations.
     *
     * @param array[] $rawOperations
     * @return OperationInterface[]
     * @throws InvalidOperationException
     */
    public static function parse(array $rawOperations): array
    {
        $operations = [];

        foreach ($rawOperations as $index => $operation) {
            // Verify that each operation includes the required members
            // https://datatracker.ietf.org/doc/html/rfc6902#section-4
            if (!is_array($operation)) {
                throw new InvalidOperationException("Operation {$index} is invalid.");
            }

            if (!array_key_exists("op", $operation)) {
                throw InvalidOperationException::missingField($index, "op");
            }

            if (!array_key_exists("path", $operation)) {
                throw InvalidOperationException::missingField($index, "path");
            }

            switch ($operation["op"]) {
                case self::ADD_OPERATION:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.1
                    if (!array_key_exists("value", $operation)) {
                        throw InvalidOperationException::missingField($index, "value");
                    }

                    $operations[] = new AddOperation($index, $operation["path"], $operation["value"]);
                    break;

                case self::COPY_OPERATION:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.5
                    if (!array_key_exists("from", $operation)) {
                        throw InvalidOperationException::missingField($index, "from");
                    }

                    $operations[] = new CopyOperation($index, $operation["path"], $operation["from"]);
                    break;

                case self::MOVE_OPERATION:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.4
                    if (!array_key_exists("from", $operation)) {
                        throw InvalidOperationException::missingField($index, "from");
                    }

                    $operations[] = new MoveOperation($index, $operation["path"], $operation["from"]);
                    break;

                case self::REMOVE_OPERATION:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.2
                    $operations[] = new RemoveOperation($index, $operation["path"]);
                    break;

                case self::REPLACE_OPERATION:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.3
                    if (!array_key_exists("value", $operation)) {
                        throw InvalidOperationException::missingField($index, "value");
                    }

                    $operations[] = new ReplaceOperation($index, $operation["path"], $operation["value"]);
                    break;

                case self::TEST_OPERATION:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.6
                    if (!array_key_exists("value", $operation)) {
                        throw InvalidOperationException::missingField($index, "value");
                    }

                    $operations[] = new TestOperation($index, $operation["path"], $operation["value"]);
                    break;

                default:
                    // Only the pre-defined operations should be accepted
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4
                    throw new InvalidOperationException("Unsupported operation at {$index}.");
            }
        }

        return $operations;
    }
}
