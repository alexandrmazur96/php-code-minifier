<?php

declare(strict_types=1);

namespace Fixtures\ActualFiles;

use function Stubs\count;

final class PhpClass
{
    public function binarySearch(array $arr, int|float $x): int|float
    {
        $low = 0;
        $high = count($arr) - 1;

        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);
            if ($arr[$mid] == $x) {
                return $mid;
            } else {
                if ($arr[$mid] < $x) {
                    $low = $mid + 1;
                } else {
                    $high = $mid - 1;
                }
            }
        }

        return -1;
    }
}