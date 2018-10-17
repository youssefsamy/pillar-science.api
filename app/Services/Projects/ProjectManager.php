<?php

namespace App\Services\Projects;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Team;
use App\Models\User;

class ProjectManager
{
    /**
     * @param Team $team Team to which the project will belong to
     * @param User $user The User that first created the project
     * @param array $attributes The attributes to pass to the project model
     * @throws \Throwable
     * @return Project
     */
    public function create(Team $team, User $user, array $attributes)
    {
        /** @var Project $project */
        $project = Project::make($attributes);
        $project->created_by = $user->id;

        \DB::transaction(function () use ($team, $user, $project) {
            $team->projects()->save($project);
            $user->projects()->save($project, ['role' => ProjectUser::ROLE_MANAGER]);
        });

        return $project;
    }
}