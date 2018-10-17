<?php

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use Illuminate\Http\Response;

class VerifyGlobalPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::user() && Auth::user()->hasGlobalPermission($permission)) {
            return $next($request);
        }

        return App::abort(Response::HTTP_FORBIDDEN, 'Access denied');
    }
}
