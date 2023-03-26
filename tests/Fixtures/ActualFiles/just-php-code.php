<?php

declare(strict_types=1);

function longestCommonSubsequence($str1, $str2) {
    $m = strlen($str1);
    $n = strlen($str2);
    $lcs = array();

    // Initialize the LCS table
    for ($i = 0; $i <= $m; $i++) {
        for ($j = 0; $j <= $n; $j++) {
            $lcs[$i][$j] = 0;
        }
    }

    // Fill the LCS table
    for ($i = 1; $i <= $m; $i++) {
        for ($j = 1; $j <= $n; $j++) {
            if ($str1[$i-1] == $str2[$j-1]) {
                $lcs[$i][$j] = $lcs[$i-1][$j-1] + 1;
            } else {
                $lcs[$i][$j] = max($lcs[$i-1][$j], $lcs[$i][$j-1]);
            }
        }
    }

    // Backtrack to find the LCS
    $i = $m;
    $j = $n;
    $result = '';
    while ($i > 0 && $j > 0) {
        if ($str1[$i-1] == $str2[$j-1]) {
            $result = $str1[$i-1] . $result;
            $i--;
            $j--;
        } else if ($lcs[$i-1][$j] > $lcs[$i][$j-1]) {
            $i--;
        } else {
            $j--;
        }
    }

    return $result;
}