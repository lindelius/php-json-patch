<?php

namespace Lindelius\JsonPatch\Utility;

use Lindelius\JsonPatch\Exception\InvalidPathException;

use function array_pop;
use function array_shift;
use function array_values;
use function explode;
use function str_replace;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6901
 */
final class JsonPointerUtility
{
    private const ENCODED_FORWARD_SLASH = "~1";
    private const ENCODED_TILDE = "~0";

    /**
     * Compile all unique parent paths for a given set of JSON Pointer paths.
     *
     * @param string[] $paths
     * @return string[]
     * @throws InvalidPathException
     */
    public static function compileParentPaths(array $paths): array
    {
        $parentPaths = [];

        if ($paths) {
            $parentPaths[] = "/";
        }

        foreach ($paths as $path) {
            $segments = self::parse($path);
            array_pop($segments);

            $segmentPrefix = "";

            foreach ($segments as $segment) {
                $segment = str_replace(["~", "/"], [self::ENCODED_TILDE, self::ENCODED_FORWARD_SLASH], $segment);
                $segment = $segmentPrefix . "/" . $segment;

                $parentPaths[$segment] = $segment;
                $segmentPrefix = $segment;
            }
        }

        return array_values($parentPaths);
    }

    /**
     * Parse a given JSON Pointer path into segments.
     *
     * @param string $path
     * @return string[]
     * @throws InvalidPathException
     */
    public static function parse(string $path): array
    {
        if ($path === "/") {
            return [];
        }

        self::ensureValidFormat($path);

        // Split the path, and then drop the root segment
        $segments = explode("/", $path);
        array_shift($segments);

        // https://datatracker.ietf.org/doc/html/rfc6901#section-3
        // https://datatracker.ietf.org/doc/html/rfc6901#section-4
        foreach ($segments as &$segment) {
            $segment = str_replace(self::ENCODED_FORWARD_SLASH, "/", $segment);
            $segment = str_replace(self::ENCODED_TILDE, "~", $segment);
        }

        return $segments;
    }

    /**
     * Ensure that a given JSON Pointer path is correctly formatted.
     *
     * @param string $path
     * @return void
     * @throws InvalidPathException
     */
    public static function ensureValidFormat(string $path): void
    {
        if (strpos($path, "/") !== 0) {
            throw new InvalidPathException("The path must start with a forward slash.");
        }

        if (preg_match("/\s+/", $path)) {
            throw new InvalidPathException("The path must not contain any whitespace.");
        }

        if (strpos($path, "//") !== false) {
            throw new InvalidPathException("The path must not contain any empty segments.");
        }
    }
}
