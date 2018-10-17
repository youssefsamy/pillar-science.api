<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Can create a project into a specific team if the user is a member
     * of the team regardless of his role in the team.
     *
     * @param User $user
     * @param Team $team
     * @return bool
     */
    public function store(User $user, Team $team)
    {
        return $user->teams->contains(function ($t) use ($team) {
            return $t->id === $team->id;
        });
    }

    public function show(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_VIEWER,
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    public function showUser(User $user, Project $project, User $targetUser)
    {
        return $user->id === $targetUser->id && $user->hasAnyRoleOnProject([
                ProjectUser::ROLE_VIEWER,
                ProjectUser::ROLE_CONTRIBUTOR,
                ProjectUser::ROLE_MANAGER
            ], $project);
    }

    /**
     * Can view a dataset
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function viewDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_VIEWER,
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * Can update dataset if the user is the creator of the project (subject to change)
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function updateDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * Can upload dataset if the user is the creator of the project (subject to change)
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function uploadDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * Can destroy dataset if the user is the creator of the project (subject to change)
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function destroyDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * If a user can move a dataset into or out of a project
     *
     * @param User $user
     * @param Project $project
     */
    public function moveDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * If a user can map a dataset into or out of a project
     *
     * @param User $user
     * @param Project $project
     */
    public function mapDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * If a user can add metadata to a project dataset
     *
     * @param User $user
     * @param Project $project
     */
    public function addMetadataDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * If a user can add protocols to a project dataset
     *
     * @param User $user
     * @param Project $project
     */
    public function addProtocolDataset(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_CONTRIBUTOR,
            ProjectUser::ROLE_MANAGER
        ], $project);
    }

    /**
     * Can update if the user is the creator of the project. (subject to change)
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function update(User $user, Project $project, array $attributes)
    {
        return $user->isAllowedToUpdateAllFields(array_keys($attributes), $project);
    }

    public function share(User $user, Project $project, string $role)
    {
        return $user->canShareAtRoleLevel($role, $project);
    }

    public function unshare(User $user, Project $project, User $targetUser)
    {
        return $user->canUnshareAtRoleLevel($targetUser, $project);
    }

    /**
     * Can destroy if the user is the creator of the project. (subject to change)
     *
     * @param User $user
     * @param Project $project
     */
    public function destroy(User $user, Project $project)
    {
        return $user->hasAnyRoleOnProject([
            ProjectUser::ROLE_MANAGER
        ], $project);
    }
}
