<?php

namespace Lindelius\JsonPatch\Utility;

use Lindelius\JsonPatch\Exception\InvalidPathException;

use function array_shift;
use function explode;
use function str_replace;

/**
 * @internal
 * @link https://datatracker.ietf.org/doc/html/rfc6901
 */
final class JsonPointerUtility
{
    /**
     * Parse a given JSON Pointer path into segments.
     *
     * @param string $path
     * @return string[]
     * @throws InvalidPathException
     */
    public static function parse(string $path): array
    {
        $segments = explode("/", $path);

        if (array_shift($segments) !== "" || empty($segments)) {
            throw new InvalidPathException("The path must start with a forward slash, \"/\".");
        }

        // https://datatracker.ietf.org/doc/html/rfc6901#section-3
        // https://datatracker.ietf.org/doc/html/rfc6901#section-4
        foreach ($segments as &$segment) {
            $segment = str_replace("~1", "/", $segment);
            $segment = str_replace("~0", "~", $segment);
        }

        return $segments;
    }
}
