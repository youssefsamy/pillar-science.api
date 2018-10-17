<?php

namespace App\Api\V1\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MetzWeb\Instagram\Instagram;
use Socialite;
use Config;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SocialAuthController extends Controller
{
    //
    public function redirect($social)
    {
        $scopes = [];
        if ($social == 'facebook')
            return Socialite::driver($social)->scopes(['email', 'publish_actions'])->redirect();
        else
            return Socialite::driver($social)->redirect();
    }

    public function callback($social)
    {
        $user = Socialite::driver($social)->user();

        // return response()->json([
        //     'user' => $user
        // ]);

        // if ($social == 'facebook') {

        // } else if ($social == 'google') {
        //     $userinfo = array('email' => $user->email, 'firstName' => $user->user->name->givenName, 'lastName' => $user->user->name->familyName);
        // } else if ($social == 'linkedin') {
        //     $userinfo = array('email' => $user->email, 'firstName' => $user->user->firstName, 'lastName' => $user->user->lastName);
        // }

        // Auth::login($user, true);
        $authUser = $this->findOrCreateUser($user);

        $token = Auth::guard()->login($authUser, true);
        
        if(!$token) {
            throw new AccessDeniedHttpException();
        }

        $expires_in = Auth::guard()->factory()->getTTL() * 60;

        return ("<script>
            window.opener.postMessage({type: 'social_login', success: true, token: '".$token."', expires_in: ".$expires_in."}, \"*\");
            window.close();
        </script>");

        return response()->json([
            'status' => 'ok',
            'user' => $token,
            'expires_in' => Auth::guard()->factory()->getTTL() * 60,
        ]);
    }

    public function findOrCreateUser($user) {
        $authUser = User::where('email', $user->email)->first();

        if ($authUser) {
            return $authUser;
        }

        return User::create([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
