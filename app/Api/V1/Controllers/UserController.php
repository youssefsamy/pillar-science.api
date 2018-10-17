<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\UserUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class UserController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource User
 */
class UserController extends Controller
{
    /**
     * Me
     *
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        /** @var User $user */
        $user = Auth::guard()->user();

        if (!$user) {
            return response()->json(null);
        }

        return response()->json($user->makeVisible('email')->load('teams'));
    }

    public function index()
    {
        return response()->json(User::with('teams')->get()->makeVisible('email')->toJson());
    }

    public function show(User $user)
    {
        return response()->json($user->load(['teams', 'invitations' => function (HasMany $query) {
            $query->orderBy('created_at', 'desc');
        }])->makeVisible(['email', 'invitations'])->toJson());
    }

    public function update(User $user, UserUpdateRequest $request)
    {
        $user->update($request->except('email'));
        $user->permissions = $request->get('permissions', []);

        $userTeams = collect($request->get('teams', []))->map(function ($teamId) {
            return [
                'id' => $teamId,
                'role' => 'user'
            ];
        });

        $adminTeams = collect($request->get('teamsAdmin', []))->map(function ($teamId) {
            return [
                'id' => $teamId,
                'role' => 'admin'
            ];
        });

        $teams = $userTeams->merge($adminTeams)
            ->mapWithKeys(function ($team) {
                return [$team['id'] => [
                    'role' => $team['role']
                ]];
            });

        $user->teams()->sync($teams);

        $user->save();

        return response()->json();
    }

    /**
     * List all available user permissions. Requires to be super-admin
     */
    public function permissions()
    {
        return response()->json(User::PERMISSIONS);
    }
}
