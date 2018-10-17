<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function notFoundResponse($attributes = [])
    {
        return response()->json(array_merge([
            'message' => 'Resource not found',
            'status_code' => Response::HTTP_NOT_FOUND
        ], $attributes), Response::HTTP_NOT_FOUND);
    }
}
