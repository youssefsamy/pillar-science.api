<?php

namespace App\Models;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @SWG\Definition (
 *     type="object",
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         example="123"
 *     ),
 *     @SWG\Property(
 *         property="name",
 *         type="string",
 *         example="Johnny Depp"
 *     ),
 *     @SWG\Property(
 *         property="permissions",
 *         type="array",
 *         @SWG\Items(
 *             type="string"
 *         )
 *     )
 * )
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-write mixed $password
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property array|null $permissions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\JoinInvitation[] $invitations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $teams
 */
class User extends Authenticatable implements JWTSubject, AuditableInterface
{
    use Auditable;
    use Notifiable;
    use SerializesDates;
    use PivotEventTrait;

    const PERMISSION_USER_TEAM_MANAGEMENT = 'user-team-management';
    const PERMISSION_DESKTOP_CLIENT_MANAGEMENT = 'desktop-client-management';

    const PERMISSIONS = [
        self::PERMISSION_USER_TEAM_MANAGEMENT,
        self::PERMISSION_DESKTOP_CLIENT_MANAGEMENT
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password',
    ];

    protected $visible = [
        'id', 'created_at', 'updated_at', 'permissions', 'teams', 'pivot'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    protected $attributes = [
        'permissions' => '[]'
    ];

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Returns true if user is website owner
     */
    public function isSuperAdmin()
    {
        return in_array(self::PERMISSION_USER_TEAM_MANAGEMENT, $this->permissions ?? []);
    }

    /**
     * @return bool
     */
    public function getHasAdminDashboardAccessAttribute() : bool
    {
        $intersection = array_intersect($this->permissions, [
            self::PERMISSION_USER_TEAM_MANAGEMENT,
            self::PERMISSION_DESKTOP_CLIENT_MANAGEMENT
        ]);

        return count($intersection) > 0;
    }

    public function invitations()
    {
        return $this->hasMany(JoinInvitation::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->using(TeamUser::class)->withPivot('role');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->using(ProjectUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function protocols()
    {
        return $this->hasMany(Protocol::class);
    }

    /**
     * @param array $fieldnames array_keys of attributes will generally do the trick
     * @param Project $project
     * @return bool
     */
    public function isAllowedToUpdateAllFields(array $fieldnames, Project $project)
    {
        if (!$project->pivot) {
            $projectWithPivot = $this->projects()->find($project->id);
        } else {
            $projectWithPivot = $project;
        }

        if (!$projectWithPivot) {
            return false;
        }

        $role = $projectWithPivot->pivot->role;

        if (!array_key_exists($role, Project::$updateFieldsPerRole)) {
            return false;
        }

        // If all fieldnames are included the field list will subtract all field.
        return count(array_diff($fieldnames, Project::$updateFieldsPerRole[$role])) === 0;
    }

    public function isAllowedToUpdateField(string $fieldname, Project $project)
    {
        if (!$project->pivot) {
            $projectWithPivot = $this->projects()->find($project->id);
        } else {
            $projectWithPivot = $project;
        }

        if (!$projectWithPivot) {
            return false;
        }

        $role = $projectWithPivot->pivot->role;

        if (!array_key_exists($role, Project::$updateFieldsPerRole)) {
            return false;
        }

        return in_array($fieldname, Project::$updateFieldsPerRole[$role]);
    }

    public function hasAnyRoleOnProject(array $roles, Project $project)
    {
        if (!$project->pivot) {
            $projectWithPivot = $this->projects()->find($project->id);
        } else {
            $projectWithPivot = $project;
        }

        if (!$projectWithPivot) {
            return false;
        }

        return in_array($projectWithPivot->pivot->role, $roles);
    }

    public function hasRoleOnProject(string $role, Project $project)
    {
        if (!$project->pivot) {
            $projectWithPivot = $this->projects()->find($project->id);
        } else {
            $projectWithPivot = $project;
        }

        if (!$projectWithPivot) {
            return false;
        }

        return $role === $projectWithPivot->pivot->role;
    }

    /**
     * @param string $requestingRoleLevel One of the ProjectUser roles
     * @param Project $project
     */
    public function canShareAtRoleLevel(string $requestingRoleLevel, Project $project) : bool
    {
        /** @var Project $projectWithPivot */
        $projectWithPivot = $this->projects()->find($project->id);

        if (!$projectWithPivot) {
            return false;
        }

        $allowedRoles = ProjectUser::$roleAssignationAllowance[$projectWithPivot->pivot->role] ?? [];

        return in_array($requestingRoleLevel, $allowedRoles);
    }

    /**
     * Verifies that the user that is requesting the unshare is allowed to demote the target user
     *
     * @param User $targetUser
     * @param Project $project
     * @return bool
     */
    public function canUnshareAtRoleLevel(User $targetUser, Project $project)
    {
        $targetUserWithPivot = $targetUser->projects()->find($project->id);

        if (!$targetUserWithPivot) {
            return false;
        }

        return $this->canShareAtRoleLevel($targetUserWithPivot->pivot->role, $project);
    }

    public function hasGlobalPermission($permission)
    {
        return in_array($permission, $this->permissions);
    }
}
