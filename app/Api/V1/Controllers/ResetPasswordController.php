<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ResetPasswordRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use Config;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Hash;
use Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ResetPasswordController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Authentication
 */
class ResetPasswordController extends Controller
{
    /**
     * Reset Password
     *
     * Resets a user's password
     *
     * @param ResetPasswordRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request, JWTAuth $JWTAuth)
    {

        // $response = $this->broker()->reset(
        //     $credential, function ($user, $password) {
        //         $this->reset($user, $password);
        //     }
        // );

        // if($response !== Password::PASSWORD_RESET) {
        //     throw new HttpException(500);
        // }

        // if(!Config::get('boilerplate.reset_password.release_token')) {
        //     return response()->json([
        //         'status' => 'ok',
        //     ]);
        // }

        //$user = User::where('email', '=', $credential('email'))->first();

        // return response()->json([
        //     'status' => 'ok',
        //     'token' => $JWTAuth->fromUser($user)
        // ]);

        $credential = $this->credentials($request);

        if (!$credential['email']) {
            return response()->json([
                'status' => false,
                'message' => 'Token invalid'
            ]);
        }

        $user = User::where('email', '=', $credential['email'])->first();

        $this->reset($user, $credential['password']);

        if(!Config::get('boilerplate.reset_password.release_token')) {
            return response()->json([
                'status' => 'ok',
            ]);
        }

        $token = Auth::guard()->attempt(array('email' => $credential['email'], 'password' => $credential['password']));
        
        if(!$token) {
            throw new AccessDeniedHttpException();
        }

        return response()->json([
            'status' => 'ok',
            'token' => $token,
            'expires_in' => Auth::guard()->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  ResetPasswordRequest  $request
     * @return array
     */
    protected function credentials(ResetPasswordRequest $request)
    {
        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $credentials['email'] = false;

        $passwordresets = PasswordReset::all();

        foreach($passwordresets as $record) {
            if (Hash::check($request->get('token'), $record->token)) {
                $credentials['email'] = $record['email'];

                return $credentials;
            }
        }

        return $credentials;
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function reset($user, $password)
    {
        $user->password = $password;
        $user->save();
    }
}
