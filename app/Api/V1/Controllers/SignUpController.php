<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\SignUpRequest;
use App\Http\Controllers\Controller;
use App\Models\JoinInvitation;
use App\Models\User;
use Config;
use Tymon\JWTAuth\JWTAuth;
use Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
/**
 * Class SignUpController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Authentication
 */
class SignUpController extends Controller
{
    public function __construct()
    {
        //$this->middleware(['auth:api', 'admin:' . User::PERMISSION_USER_TEAM_MANAGEMENT]);
    }

    /**
     * Sign Up
     *
     * Sign up a new user
     *
     * @param SignUpRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUpOld(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User($request->all());

        $user->permissions = $request->get('permissions', []);
        $user->password = md5(uniqid());

        $user->save();

        $user->teams()->attach($request->get('teamsAdmin', []), ['role' => 'admin']);
        $user->teams()->attach($request->get('teams', []), ['role' => 'user']);

        $invitation = JoinInvitation::generate();

        $user->invitations()->save($invitation);

        $invitation->sendNotification();

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }

    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User($request->all());

        $user->permissions = $request->get('permissions', []);
        
        $user->save();

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        // $token = $JWTAuth->fromUser($user);
        // return response()->json([
        //     'status' => 'ok',
        //     'token' => $token
        // ], 201);

        $credentials = $request->only(['email', 'password']);
        
        $token = Auth::guard()->attempt($credentials);

        if(!$token) {
            throw new AccessDeniedHttpException();
        }

      

        return response()
            ->json([
                'status' => 'ok',
                'token' => $token,
                'expires_in' => Auth::guard()->factory()->getTTL() * 60,
            ]);

    }
}
