<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ConsumeInvitationRequest;
use App\Events\UserAcceptedInvitationEvent;
use App\Http\Controllers\Controller;
use App\Models\JoinInvitation;

/**
 * Class JoinInvitationController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Join Invitation
 */
class JoinInvitationController extends Controller
{
    /**
     * Show
     *
     * Show a single invitation based on the provided token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/invitations/{token}",
     *     summary="Create team",
     *     operationId="api.invitations.show",
     *     produces={"application/json"},
     *     tags={"join_invitations"},
     *     @SWG\Parameter(
     *         name="token",
     *         required=true,
     *         type="string",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Team info",
     *         @SWG\Schema(
     *             ref="#/definitions/Team"
     *         ),
     *     ),
     * )
     */
    public function show(string $token)
    {
        /** @var JoinInvitation $invitation */
        $invitation = JoinInvitation::active()
            ->whereToken($token)
            ->with('user.teams')
            ->first();

        if (!$invitation) {
            return $this->notFoundResponse();
        }

        $invitation->user->makeVisible('email');

        return response()->json($invitation);
    }

    /**
     * Consume the join invitation and sets the user password
     *
     * @param string $token
     *
     * @SWG\Post(
     *     path="/invitations/{token}/consume",
     *     summary="Create team",
     *     operationId="api.invitations.consume",
     *     produces={"application/json"},
     *     tags={"join_invitations"},
     *     @SWG\Parameter(
     *         name="token",
     *         required=true,
     *         type="string",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         required=true,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Consumed token"
     *     ),
     * )
     */
    public function consume(ConsumeInvitationRequest $request, string $token)
    {
        /** @var JoinInvitation $invitation */
        $invitation = JoinInvitation::active()
            ->whereToken($token)
            ->first();

        if (!$invitation) {
            return $this->notFoundResponse();
        }

        $invitation->user->password = $request->get('password');
        $invitation->user->save();

        $invitation->markAsConsumed();

        event(new UserAcceptedInvitationEvent($invitation));

        return response()->json();
    }
}
