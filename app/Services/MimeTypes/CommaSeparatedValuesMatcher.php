<?php

namespace App\Services\MimeTypes;

/**
 * Class CommaSeparatedValuesMatcher
 *
 * @package App\Services\MimeTypes
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 */
class CommaSeparatedValuesMatcher implements MimeTypeMatcherInterface
{
    // Matches all commas except if preceded by a backslash (escape character)
    const PATTERN = '/(?<!\\\\)[,]/';
    const MIME_TYPE = 'text/csv';
    const DELIMITER = ',';

    public static function match(array $lines = [], $fileExtension = null)
    {
        if (count($lines) <= 1) {
            return null;
        }

        $lastDelimiterCount = -1;

        foreach ($lines as $line) {
            // Ignore empty lines
            $line = trim($line, " \n\r\0\x0B");
            if (empty($line)) {
                continue;
            }

            preg_match_all(self::PATTERN, $line, $matches);
            $currentDelimiterCount = count($matches[0]);

            if ($lastDelimiterCount < 0) {
                $lastDelimiterCount = $currentDelimiterCount;
            }

            if ($lastDelimiterCount !== $currentDelimiterCount || $lastDelimiterCount === 0) {
                return null;
            }
        }

        return true;
    }
}