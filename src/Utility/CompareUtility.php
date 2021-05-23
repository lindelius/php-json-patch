<?php

namespace Lindelius\JsonPatch\Utility;

use function array_is_list;
use function is_array;
use function json_encode;
use function ksort;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6902#section-4.6
 */
final class CompareUtility
{
    /**
     * Compare two values according to the JSON Patch equality rules.
     *
     * @param mixed $expected
     * @param mixed $value
     * @return bool
     */
    public static function equals($expected, $value): bool
    {
        self::recursivePrepare($expected);
        self::recursivePrepare($value);

        return json_encode($expected) === json_encode($value);
    }

    /**
     * Recursively prepare a given value to be equality checked according to
     * the JSON Patch equality rules.
     *
     * @param mixed $value
     * @return void
     */
    private static function recursivePrepare(&$value): void
    {
        // We are only interested in modifying arrays
        if (!is_array($value)) {
            return;
        }

        // Associative arrays, i.e. JSON objects, must be sorted by keys before
        // any equality checks are made.
        if (!array_is_list($value)) {
            ksort($value);
        }

        // Recursively prepare any nested arrays
        foreach ($value as $nestedValue) {
            if (is_array($nestedValue)) {
                self::recursivePrepare($nestedValue);
            }
        }
    }
}
