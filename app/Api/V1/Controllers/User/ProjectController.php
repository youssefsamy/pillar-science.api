<?php

namespace App\Api\V1\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectController extends Controller
{
    public function show(User $user, Project $project)
    {
        $this->authorize('show-user', [$project, $user]);

        $projectWithPivot = $user->projects()->find($project->id);

        return response()->json($projectWithPivot->load('team'));
    }
}