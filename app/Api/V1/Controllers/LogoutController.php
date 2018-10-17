<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Auth;

/**
 * Class LogoutController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Authentication
 */
class LogoutController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', []);
    }

    /**
     * Logout
     *
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/auth/logout",
     *     summary="Logout from the platform",
     *     operationId="api.auth.logout",
     *     produces={"application/json"},
     *     tags={"auth"},
     *     @SWG\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Successfully logged out"
     *             ),
     *         ),
     *     ),
     * )
     */
    public function logout()
    {
        Auth::guard()->logout();

        return response()
            ->json(['message' => 'Successfully logged out']);
    }
}
