<?php

if (!function_exists("array_is_list")) {
    /**
     * Check whether a given array is considered a "list".
     *
     * @param array $array
     * @return bool
     */
    function array_is_list(array $array): bool
    {
        $expectedIndex = 0;

        foreach ($array as $index => $value) {
            if ($index !== $expectedIndex) {
                return false;
            }

            $expectedIndex++;
        }

        return true;
    }
}
