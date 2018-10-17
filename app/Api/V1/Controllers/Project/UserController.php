<?php

namespace App\Api\V1\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserController
 *
 * @package App\Api\V1\Controllers\Project
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 */
class UserController extends Controller
{
    /**
     * Query users by name or email (email exact match)
     */
    public function availableAutocomplete(Project $project, Request $request)
    {
        $query = $request->get('query', '');

        $users = User::limit(5)
            ->where(function (Builder $q) use ($query) {
                $q->where('name', 'like', '%' . $query .'%');
                $q->orWhere('email', $query);
            })
            ->whereNotIn('id', $project->users->pluck('id'))
            ->get();

        return response()->json($users);
    }

    public function index(Project $project)
    {
        $this->authorize('show', $project);

        return response()->json($project->users);
    }

    public function update(Project $project, User $user, Request $request)
    {
        $role = $request->get('role');
        $this->authorize('share', [$project, $role]);

        $project->shareWith($user, $role);

        return response()->json($project->users()->find($user->id));
    }

    public function destroy(Project $project, User $user, Request $request)
    {
        $this->authorize('unshare', [$project, $user]);

        $project->stopSharingWith($user);

        return response()->json($user);
    }
}