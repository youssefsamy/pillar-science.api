<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\TeamCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * Class TeamController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Team
 */
class TeamController extends Controller
{
    /**
     * Requires to be a platform admin to use. Searches a team on the platform. For now it only lists all
     * the teams on the platform.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/teams/search",
     *     summary="Search for a team",
     *     operationId="api.teams.search",
     *     produces={"application/json"},
     *     tags={"teams"},
     *     @SWG\Response(
     *         response=200,
     *         description="Team results",
     *         @SWG\Schema(
     *             @SWG\Items(
     *                 ref="#/definitions/Team"
     *             )
     *         ),
     *     ),
     * )
     */
    public function search()
    {
        return response()->json(Team::with('members')->get());
    }

    /**
     * List all the logged in user's teams.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/teams",
     *     summary="List a user's teams",
     *     operationId="api.teams.index",
     *     produces={"application/json"},
     *     tags={"teams"},
     *     @SWG\Response(
     *         response=200,
     *         description="Team results",
     *         @SWG\Schema(
     *             @SWG\Items(
     *                 ref="#/definitions/Team"
     *             )
     *         ),
     *     ),
     * )
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth()->user();

        return response()->json($user->teams()->with('members')->get());
    }

    /**
     * Show the details of a single team
     *
     * @param Team $team
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/teams/{team}",
     *     summary="Single team",
     *     operationId="api.teams.show",
     *     produces={"application/json"},
     *     tags={"teams"},
     *     @SWG\Parameter(
     *         name="team",
     *         required=true,
     *         type="integer",
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
    public function show(Team $team)
    {
        return response()->json($team);
    }

    /**
     * Create
     *
     * Creates a new team
     *
     * @param TeamCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/teams",
     *     summary="Create team",
     *     operationId="api.teams.show",
     *     produces={"application/json"},
     *     tags={"teams"},
     *     @SWG\Parameter(
     *         name="name",
     *         required=true,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Team info",
     *         @SWG\Schema(
     *             ref="#/definitions/Team"
     *         ),
     *     ),
     * )
     */
    public function store(TeamCreateRequest $request)
    {
        $team = Team::create($request->all());

        return response()->json([
            'id' => $team->id
        ], Response::HTTP_CREATED);
    }

    /**
     * Updates the details of a team
     *
     * @param Team $team
     * @param TeamCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="/teams/{team}",
     *     summary="Update team",
     *     operationId="api.teams.show",
     *     produces={"application/json"},
     *     tags={"teams"},
     *     @SWG\Parameter(
     *         name="team",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         required=true,
     *         type="string",
     *         in="formData"
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
    public function update(Team $team, TeamCreateRequest $request)
    {
        $team->update($request->all());

        return response()->json(null);
    }

    /**
     * Delete
     *
     * Deletes a team
     *
     * @param Team $team
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     *
     * @SWG\Delete(
     *     path="/teams/{team}",
     *     summary="Update team",
     *     operationId="api.teams.show",
     *     produces={"application/json"},
     *     tags={"teams"},
     *     @SWG\Parameter(
     *         name="team",
     *         required=true,
     *         type="integer",
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
    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(null);
    }
}
