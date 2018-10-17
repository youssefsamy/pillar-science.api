<?php

namespace App\Api\V1\Controllers\Team;

use App\Api\V1\Requests\ProjectStoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\Projects\ProjectManager;
use Illuminate\Http\Response;

/**
 * Class ProjectController
 *
 * @package App\Api\V1\Controllers\Team
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Project
 */
class ProjectController extends Controller
{
    /**
     * [TEAM] Create project
     *
     * Creates a project and associate it to the team
     *
     * @param Team $team
     * @param ProjectStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(Team $team, ProjectStoreRequest $request, ProjectManager $manager)
    {
        // @see https://stackoverflow.com/questions/36482737/laravel-policies-how-to-pass-multiple-arguments-to-function
        $this->authorize('store', [Project::class, $team]);

        /** @var User $user */
        $user = Auth()->user();

        $project = $manager->create($team, $user, $request->all());

        return response()->json($project, Response::HTTP_CREATED);
    }
}
