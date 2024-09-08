<?php

namespace App\Helpers;

class ArrayHelper
{
    /**
     * Filter an array by keeping only the specified keys.
     *
     * @param array $array
     * @param array $keysToKeep
     * @return array
     */
    public static function filterArrayByKeys(array $array, array $keysToKeep): array
    {
        return array_map(function ($item) use ($keysToKeep) {
            return array_intersect_key($item, array_flip($keysToKeep));
        }, $array);
    }
}