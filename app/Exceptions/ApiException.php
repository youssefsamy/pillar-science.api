<?php

namespace App\Exceptions;

class ApiException extends \Exception
{
    public $internalMessage;

    public $statusCode = 500;

    public static function make(array $options)
    {
        $exception = new static;

        foreach ($options as $key => $value) {
            $exception->$key = $value;
        }

        return $exception;
    }

    public function __set($name, $value)
    {
        // Do not allow arbitrary fields
    }

    public function render()
    {
        return [
            'error' => [
                'message' => $this->message,
                'status_code' => $this->statusCode
            ]
        ];
    }
}