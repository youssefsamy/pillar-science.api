<?php

namespace App\Models;

use App\Events\ProjectSharedEvent;
use App\Events\ProjectUnsharedEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\Project
 *
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
 *         example="My fabulous project"
 *     ),
 *     @SWG\Property(
 *         property="created_by",
 *         type="definition",
 *         ref="#/definitions/User"
 *     )
 * )
 *
 * @property int $id
 * @property string $name
 * @property int $team_id
 * @property int $created_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read \App\Models\Team $team
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project forUser(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Kalnoy\Nestedset\Collection|\App\Models\Dataset $directory
 * @property-read \App\Models\Dataset $dataset
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Project onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Project withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Project withoutTrashed()
 */
class Project extends Model implements AuditableInterface
{
    use Auditable;
    use SerializesDates;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description'
    ];

    protected $visible = [
        'id',
        'name',
        'description',
        'user',
        'team',
        'created_at',
        'updated_at',
        'author',
        'pivot'
    ];

    public static $updateFieldsPerRole = [
        ProjectUser::ROLE_CONTRIBUTOR => [
            'description'
        ],
        ProjectUser::ROLE_MANAGER => [
            'name',
            'description'
        ]
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = \Purifier::clean($value);
    }

    public function scopeForUser(Builder $builder, User $user)
    {
        $builder
            ->select('projects.*')
            ->join('teams', 'projects.team_id', '=', 'teams.id')
            ->join('team_user', 'teams.id', '=', 'team_user.team_id')
            ->join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->where('project_user.user_id', $user->id)
            ->where('team_user.user_id', $user->id);
    }

    public function directory()
    {
        return $this->hasOne(Dataset::class);
    }

    /**
     * In a sharing perspective
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(ProjectUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Shares with user with a given role. If it is already shared with that user
     * it will update the role for that user.
     *
     * @param User $user
     * @param string $role
     * @param array $additionalPivotAttributes
     * @return static
     */
    public function shareWith(User $user, string $role, array $additionalPivotAttributes = [])
    {
        $userWithPivot = $this->users()->find($user->id);

        $attributes = array_merge($additionalPivotAttributes, [
            'role' => $role
        ]);

        if (!$userWithPivot) {
            $this->users()->save($user, $attributes);
        } else {
            $this->users()->updateExistingPivot($this->id, $attributes);
        }

        event(new ProjectSharedEvent($this, $user, $role));

        return $this;
    }

    /**
     * Removes the sharing relation between a user and a project
     *
     * @param User $user
     * @return static
     */
    public function stopSharingWith(User $user)
    {
        $this->users()->detach($user);

        event(new ProjectUnsharedEvent($this, $user));

        return $this;
    }
}
