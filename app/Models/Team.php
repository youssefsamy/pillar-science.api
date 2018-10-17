<?php

namespace App\Models;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\Team
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
 *         example="My awesome team"
 *     )
 * )
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Team whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $members
 * @property-read \App\Models\Dataset $directory
 * @property-read \Kalnoy\Nestedset\Collection|\App\Models\Dataset[] $privateDirectories
 * @property-read \App\Models\Dataset $sharedDirectory
 * @property-read \Kalnoy\Nestedset\Collection|\App\Models\Dataset[] $userDirectories
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Team onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Team withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Team withoutTrashed()
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Team whereDeletedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RemoteDirectory[] $remoteDirectories
 */
class Team extends Model implements AuditableInterface
{
    use Auditable;
    use SerializesDates;
    use SoftDeletes;
    use PivotEventTrait;

    protected $fillable = ['name'];

    protected $visible = [
        'id',
        'name',
        'members',
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function users()
    {
        return $this->members()->withPivotValue('role', TeamUser::ROLE_USER);
    }

    public function admins()
    {
        return $this->users()->withPivotValue('role', TeamUser::ROLE_USER);
    }

    public function members()
    {
        return $this->belongsToMany(User::class)->using(TeamUser::class)->withPivot('role');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function userDirectories()
    {
        return $this->hasMany(Dataset::class)
            ->whereNotNull('owner_id');
    }

    public function remoteDirectories()
    {
        return $this->hasMany(RemoteDirectory::class);
    }

    public function directory()
    {
        return $this->hasOne(Dataset::class)
            ->whereNull('owner_id');
    }

    public function getResourceBasePath()
    {
        return sprintf('teams/%s', $this->id);
    }
}
