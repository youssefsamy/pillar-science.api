<?php

namespace App\Http\Middleware;

use App\Models\RemoteDirectory;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Access\Gate;

class VerifyRemoteDirectory
{
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('secret_key')) {
            $token = $request->get('secret_key');

            /** @var RemoteDirectory $remoteDirectory */
            $remoteDirectory = RemoteDirectory::where('secret_key', $token)->firstOrFail();

            $remoteDirectory->last_action_at = Carbon::now();
            $remoteDirectory->save();
        }

        return $next($request);
    }
}
