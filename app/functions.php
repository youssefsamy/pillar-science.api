<?php

if (! function_exists('str_truncate')) {
    function str_truncate($text, $maxWords = 6) : string
    {
        $allWords = explode(' ', $text);

        $words = array_slice($allWords, 0, $maxWords);

        return implode(' ', $words);
    }
}