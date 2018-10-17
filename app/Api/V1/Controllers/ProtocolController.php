<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Protocol;
use App\Models\User;
use Illuminate\Http\Request;

class ProtocolController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = \Auth::user();

        $protocols = $user->protocols()->with('user')->get();
        $protocols->makeVisible(['user', 'excerpt']);
        $protocols->makeHidden('content'); // Reduce response size
        return response()->json($protocols);
    }

    public function show(Protocol $protocol)
    {
        $this->authorize('view', $protocol);

        return response()->json($protocol);
    }

    public function update(Protocol $protocol, Request $request)
    {
        $this->authorize('update', $protocol);

        $protocol->update($request->all());

        return response()->json($protocol);
    }
}