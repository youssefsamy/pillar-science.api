<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\RemoteDirectoryUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\RemoteDirectory;

/**
 * Class RemoteDirectoryController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource RemoteDirectory
 */
class RemoteDirectoryController extends Controller
{
    /**
     * Gets information on a Remote Directory
     *
     * @param RemoteDirectory $remoteDirectory
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/remote-directories/{remote_directory}",
     *     operationId="api.remoteDirectories.get",
     *     produces={"application/json"},
     *     tags={"remote_directories"},
     *     @SWG\Parameter(
     *         name="remote_directory",
     *         description="The Remote Directory id",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Remote directory information",
     *         @SWG\Schema(
     *             ref="#/definitions/RemoteDirectory"
     *         )
     *     )
     * )
     */
    public function show(RemoteDirectory $remoteDirectory)
    {
        return response()->json($remoteDirectory->load('directory'));
    }

    /**
     * Update remote directory attributes.
     *
     * @param RemoteDirectory $remoteDirectory
     * @param RemoteDirectoryUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="/remote-directories/{remote_directory}",
     *     operationId="api.remoteDirectories.update",
     *     produces={"application/json"},
     *     tags={"remote_directories"},
     *     @SWG\Parameter(
     *         name="remote_directory",
     *         description="The Remote Directory id",
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
     *         description="Remote directory information",
     *         @SWG\Schema(
     *             ref="#/definitions/RemoteDirectory"
     *         )
     *     )
     * )
     */
    public function update(RemoteDirectory $remoteDirectory, RemoteDirectoryUpdateRequest $request)
    {
        $remoteDirectory->update($request->all());

        return response()->json($remoteDirectory);
    }
}