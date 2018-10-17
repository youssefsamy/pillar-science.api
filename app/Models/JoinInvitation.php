<?php

namespace App\Models;

use App\Notifications\JoinApplicationNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\JoinInvitation
 *
 * @property-read \App\Models\User $user
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation whereUserId($value)
 * @property string|null $consumed_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\JoinInvitation whereConsumedAt($value)
 */
class JoinInvitation extends Model implements AuditableInterface
{
    use Auditable;
    use SerializesDates;
    use SoftDeletes;

    protected $visible = [
        'user',
        'status',
        'created_at'
    ];

    protected $dates = [
        'expires_at'
    ];

    protected $appends = [
        'status'
    ];

    const STATUS_CONSUMED = 'consumed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_ACTIVE = 'active';

    public static function generate()
    {
        $invitation = new static;

        $invitation->token = md5('syft' . uniqid());
        $invitation->expires_at = Carbon::now()->addHours(config('pillar.join_invitation.expiration'));

        return $invitation;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('expires_at', '>=', Carbon::now())
            ->whereNull('consumed_at');
    }

    public function sendNotification()
    {
        $this->user->notify(new JoinApplicationNotification($this));
    }

    public function markAsConsumed()
    {
        $this->consumed_at = Carbon::now();
        $this->save();
    }

    public function getIsConsumedAttribute()
    {
        return $this->consumed_at !== null;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at < Carbon::now();
    }

    public function getStatusAttribute()
    {
        if ($this->isConsumed) {
            return self::STATUS_CONSUMED;
        } else if ($this->isExpired) {
            return self::STATUS_EXPIRED;
        }

        return self::STATUS_ACTIVE;
    }
}
