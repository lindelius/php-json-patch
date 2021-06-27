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
    private const FIELD_FROM = "from";
    private const FIELD_OP = "op";
    private const FIELD_PATH = "path";
    private const FIELD_VALUE = "value";

    private const OPERATION_ADD = "add";
    private const OPERATION_COPY = "copy";
    private const OPERATION_MOVE = "move";
    private const OPERATION_REMOVE = "remove";
    private const OPERATION_REPLACE = "replace";
    private const OPERATION_TEST = "test";

    /**
     * Parse a given set of JSON Patch operations.
     *
     * @param array[] $rawOperations
     * @return OperationInterface[]
     * @throws InvalidOperationException
     */
    public static function parse(iterable $rawOperations): iterable
    {
        $expectedIndex = 0;

        foreach ($rawOperations as $index => $operation) {
            if ($index !== $expectedIndex++) {
                throw new InvalidOperationException("Invalid operation index.");
            }

            // Verify that each operation includes the required fields.
            // https://datatracker.ietf.org/doc/html/rfc6902#section-4
            if (!is_array($operation)) {
                throw new InvalidOperationException("Operation {$index} is invalid.");
            }

            self::ensureFieldExists(self::FIELD_OP, $operation, $index);
            self::ensureFieldExists(self::FIELD_PATH, $operation, $index);

            switch ($operation[self::FIELD_OP]) {
                case self::OPERATION_ADD:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.1
                    self::ensureFieldExists(self::FIELD_VALUE, $operation, $index);

                    yield new AddOperation($index, $operation[self::FIELD_PATH], $operation[self::FIELD_VALUE]);
                    break;

                case self::OPERATION_COPY:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.5
                    self::ensureFieldExists(self::FIELD_FROM, $operation, $index);

                    yield new CopyOperation($index, $operation[self::FIELD_PATH], $operation[self::FIELD_FROM]);
                    break;

                case self::OPERATION_MOVE:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.4
                    self::ensureFieldExists(self::FIELD_FROM, $operation, $index);

                    yield new MoveOperation($index, $operation[self::FIELD_PATH], $operation[self::FIELD_FROM]);
                    break;

                case self::OPERATION_REMOVE:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.2
                    yield new RemoveOperation($index, $operation[self::FIELD_PATH]);
                    break;

                case self::OPERATION_REPLACE:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.3
                    self::ensureFieldExists(self::FIELD_VALUE, $operation, $index);

                    yield new ReplaceOperation($index, $operation[self::FIELD_PATH], $operation[self::FIELD_VALUE]);
                    break;

                case self::OPERATION_TEST:
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4.6
                    self::ensureFieldExists(self::FIELD_VALUE, $operation, $index);

                    yield new TestOperation($index, $operation[self::FIELD_PATH], $operation[self::FIELD_VALUE]);
                    break;

                default:
                    // Only the pre-defined operations should be accepted.
                    // https://datatracker.ietf.org/doc/html/rfc6902#section-4
                    throw new InvalidOperationException("Unsupported operation at {$index}.");
            }
        }
    }

    /**
     * Ensure that a given field is included with a given operation.
     *
     * @param string $field
     * @param array $operation
     * @param int $operationIndex
     * @return void
     */
    private static function ensureFieldExists(string $field, array &$operation, int $operationIndex): void
    {
        if (!array_key_exists($field, $operation)) {
            throw new InvalidOperationException("Operation {$operationIndex} is missing required \"{$field}\" field.");
        }
    }
}
