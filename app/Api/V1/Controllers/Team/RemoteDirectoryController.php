<?php

namespace App\Api\V1\Controllers\Team;

use App\Api\V1\Requests\RemoteDirectoryStoreRequest;
use App\Http\Controllers\Controller;
use App\Models\RemoteDirectory;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Class RemoteDirectoryController
 *
 * @package App\Api\V1\Controllers\Team
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource RemoteDirectory
 */
class RemoteDirectoryController extends Controller
{
    /**
     * List all remote directories that belongs to a team
     *
     * @param Team $team
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/teams/{team}/remote-directories",
     *     operationId="api.teams.remoteDirectories.index",
     *     produces={"application/json"},
     *     tags={"remote_directories"},
     *     @SWG\Parameter(
     *         name="team",
     *         description="The Team id",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="List of Remote Directories that belongs to the team",
     *         @SWG\Schema(
     *             ref="#/definitions/RemoteDirectory"
     *         )
     *     )
     * )
     */
    public function index(Team $team, Request $request)
    {
        $remoteDirectories = $team->remoteDirectories();

        if ($request->query('computer', false) !== false) {
            $remoteDirectories->where('computer_id', $request->query('computer'));
        }

        $remoteDirectories->with('directory');

        return response()->json($remoteDirectories->get());
    }

    /**
     * Create a new remote directory. The remote directory is associated to the team.
     *
     * @param Team $team
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/teams/{team}/remote-directories",
     *     operationId="api.teams.remoteDirectories.create",
     *     produces={"application/json"},
     *     tags={"remote_directories"},
     *     @SWG\Parameter(
     *         name="team",
     *         description="The Team id",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         description="The remote directory name",
     *         required=true,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Parameter(
     *         name="computer_id",
     *         description="The computer id from where the dataset comes from. Only used to group remote directories together",
     *         required=true,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Remote directory information with the secret key. This is the only time you will get the secret key",
     *         @SWG\Schema(
     *             ref="#/definitions/RemoteDirectory"
     *         )
     *     )
     * )
     */
    public function store(Team $team, RemoteDirectoryStoreRequest $request)
    {
        /** @var RemoteDirectory $remoteDirectory */
        $remoteDirectory = RemoteDirectory::make([
            'name' => $request->get('name'),
            'computer_id' => $request->get('computer_id')
        ]);

        $remoteDirectory->last_action_at = Carbon::now();
        $remoteDirectory->secret_key = uniqid("", true);

        $team->remoteDirectories()->save($remoteDirectory);

        return response()->json($remoteDirectory->makeVisible('secret_key')->load('directory'), Response::HTTP_CREATED);
    }
}