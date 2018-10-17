<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class LoginController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Authentication
 */
class LoginController extends Controller
{
    /**
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/auth/login",
     *     summary="Login into the api",
     *     description="Use the provided token from the response in the header of subsequent requests as a bearer token to access restricted areas",
     *     operationId="api.auth.login",
     *     produces={"application/json"},
     *     tags={"auth"},
     *     @SWG\Parameter(
     *         name="email",
     *         required=true,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         required=true,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Login successful",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="status",
     *                 type="string",
     *                 example="ok"
     *             ),
     *             @SWG\Property(
     *                 property="token",
     *                 type="string",
     *                 example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMTAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE1MjkzNDEwMTcsImV4cCI6MTUyOTM0NDYxNywibmJmIjoxNTI5MzQxMDE3LCJqdGkiOiJBNHpMTHdDWmZUOFRxanpGIiwic3ViIjo0LCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.rn_RD5KT3lrxi569sGDpZ9W742DYE5KkUY6g1v3fOd8"
     *             ),
     *             @SWG\Property(
     *                 property="expires_in",
     *                 type="integer",
     *                 description="In seconds",
     *                 example=3600
     *             ),
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);

        $token = Auth::guard()->attempt($credentials);

        if(!$token) {
            throw new AccessDeniedHttpException();
        }

        return response()
            ->json([
                'status' => 'ok',
                'token' => $token,
                'expires_in' => Auth::guard()->factory()->getTTL() * 60
            ]);
    }
}
