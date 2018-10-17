<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ProjectStoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Dingo\Api\Http\Request;

/**
 * Class ProjectController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Project
 */
class ProjectController extends Controller
{
    /**
     * Lists all the projects visible to a user through all his teams
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/projects",
     *     summary="List projects",
     *     operationId="api.projects.index",
     *     produces={"application/json"},
     *     tags={"projects"},
     *     @SWG\Response(
     *         response=200,
     *         description="Current user's projects",
     *         @SWG\Schema(
     *             @SWG\Items(
     *                 ref="#/definitions/Project"
     *             )
     *         ),
     *     ),
     * )
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $projects = Project::forUser($user);

        return response()->json($projects->with('team', 'author')->orderBy('created_at', 'desc')->get());
    }

    /**
     * Displays the detail of a single project
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/projects/{project}",
     *     summary="Show single project",
     *     operationId="api.projects.show",
     *     produces={"application/json"},
     *     tags={"projects"},
     *     @SWG\Parameter(
     *         name="project",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Single project view",
     *         @SWG\Schema(
     *             ref="#/definitions/Project"
     *         ),
     *     ),
     * )
     */
    public function show(Project $project)
    {
        $this->authorize('show', $project);

        return response()->json($project->load('team'));
    }

    /**
     * Update a project
     *
     * @param Project $project
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @SWG\Put(
     *     path="/projects/{project}",
     *     summary="Update project",
     *     operationId="api.projects.update",
     *     produces={"application/json"},
     *     tags={"projects"},
     *     @SWG\Parameter(
     *         name="project",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Project information",
     *         @SWG\Schema(
     *             ref="#/definitions/Project"
     *         ),
     *     ),
     * )
     */
    public function update(Project $project, ProjectStoreRequest $request)
    {
        $attributes = $request->all();

        $this->authorize('update', [$project, $attributes]);

        $project->update($attributes);

        return response()->json($project);
    }

    /**
     * Delete a project
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Project $project)
    {
        $this->authorize('destroy', $project);

        $project->delete();

        return response()->json([
            'id' => $project->id
        ]);
    }
}
