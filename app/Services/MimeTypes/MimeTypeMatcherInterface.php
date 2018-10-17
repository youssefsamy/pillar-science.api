<?php

namespace App\Services\MimeTypes;

/**
 * Interface MimeTypeMatcherInterface
 *
 * @package App\Services\MimeTypes
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 */
interface MimeTypeMatcherInterface
{
    public static function match(array $lines = [], $fileExtension = null);
}