<?php

namespace App\Exceptions;

class StorageSyncException extends ApiException implements RenderableException
{
    /** @var string */
    public $path;

    /** @var string s3, local, etc */
    public $disk = 'local';

    public $message = 'An error occurred during file synchronisation';
}