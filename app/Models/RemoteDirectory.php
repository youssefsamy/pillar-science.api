<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\RemoteFolder
 *
 * @property-read \App\Models\Team $team
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property int $team_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory wherePairingToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereUpdatedAt($value)
 * @property string|null $last_action_at
 * @property-read \App\Models\Dataset $directory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereLastActionAt($value)
 * @SWG\Definition (
 *     type="object",
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         example="1"
 *     ),
 *     @SWG\Property(
 *         property="name",
 *         type="string",
 *         example="Windows 10 in lab 404"
 *     ),
 *     @SWG\Property(
 *         property="secret_key",
 *         description="Only available at remote directory creation.",
 *         type="string",
 *         example="5aff3659a35bc0.36787558"
 *     ),
 *     @SWG\Property(
 *         property="last_action_at",
 *         type="string",
 *         example="2018-06-18T16:57:00+0000"
 *     ),
 *     @SWG\Property(
 *         property="directory",
 *         type="object",
 *         ref="#/definitions/Dataset"
 *     )
 * )
 * @property string|null $secret_key
 * @property string $computer_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereComputerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RemoteDirectory whereSecretKey($value)
 */
class RemoteDirectory extends Model implements AuditableInterface
{
    use Auditable;
    use SerializesDates;

    protected $fillable = [
        'name',
        'computer_id'
    ];

    protected $visible = [
        'id',
        'name',
        'last_action_at',
        'directory',
        'computer_id'
    ];

    protected $dates = [
        'last_action_at'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function directory()
    {
        return $this->hasOne(Dataset::class);
    }
}
