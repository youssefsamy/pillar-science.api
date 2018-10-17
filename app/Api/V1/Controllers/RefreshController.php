<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Auth;

/**
 * Class RefreshController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Authentication
 */
class RefreshController extends Controller
{
    /**
     * Refresh
     *
     * Refresh a jwt token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = Auth::guard()->refresh();

        return response()->json([
            'status' => 'ok',
            'token' => $token,
            'expires_in' => Auth::guard()->factory()->getTTL() * 60
        ]);
    }
}
