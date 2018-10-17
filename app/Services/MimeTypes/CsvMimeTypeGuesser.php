<?php

namespace App\Services\MimeTypes;

use File;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Class CsvMimeTypeGuesser
 *
 * @package App\Services\MimeTypes
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 */
class CsvMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * Number of lines to read into the file to guess the mime type. The higher
     * the more precise but longer to evaluate.
     */
    const MAX_LINES = 5;

    /** @var MimeTypeMatcherInterface[] */
    protected $matchers = [
        TabSeparatedValuesMatcher::class,
        CommaSeparatedValuesMatcher::class
    ];

    /**
     * Guesses the mime type of the file with the given path.
     *
     * @param string $path The path to the file
     *
     * @return string The mime type or NULL, if none could be guessed
     *
     * @throws FileNotFoundException If the file does not exist
     * @throws AccessDeniedException If the file could not be read
     * @throws \ReflectionException
     */
    public function guess($path)
    {
        if (! File::exists($path)) {
            return null;
        }

        if (! File::isReadable($path)) {
            return null;
        }

        $hFile = fopen($path, 'r');
        $counter = 0;
        $lines = [];

        while (($line = fgets($hFile)) !== false && $counter < self::MAX_LINES) {
            // Ignore empty lines
            if (empty($line)) {
                continue;
            }

            $lines[] = $line;

            $counter++;
        }

        fclose($hFile);

        foreach ($this->matchers as $matcher) {
            if (forward_static_call([$matcher, 'match'], $lines)) {
                return (new \ReflectionClass($matcher))->getConstant('MIME_TYPE');
            }
        }

        return null;
    }
}