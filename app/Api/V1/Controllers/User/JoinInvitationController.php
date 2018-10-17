<?php

namespace App\Api\V1\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JoinInvitation;
use App\Models\User;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class JoinInvitationController extends Controller
{
    public function store(User $user, Request $request)
    {
        $user->invitations()->active()->delete();

        $invitation = JoinInvitation::generate();

        $user->invitations()->save($invitation);

        $invitation->sendNotification();

        return response()->json(null, Response::HTTP_CREATED);
    }
}