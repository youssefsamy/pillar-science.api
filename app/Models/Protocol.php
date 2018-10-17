<?php

namespace App\Models;

use App\Models\Behaviors\SearchableByUser;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\Protocol
 *
 * @property int $id
 * @property string $name
 * @property string $content
 * @property int $user_id
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Kalnoy\Nestedset\Collection|\App\Models\Dataset[] $datasets
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Protocol whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Protocol whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Protocol whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Protocol whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Protocol whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Protocol whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Protocol whereUserId($value)
 * @mixin \Eloquent
 */
class Protocol extends Model implements AuditableInterface
{
    use Auditable;
    use SearchableByUser {
        searchable as traitSearchable;
    }

    protected $fillable = [
        'name',
        'content'
    ];

    protected $visible = [
        'id',
        'name',
        'content',
        'dataset',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'excerpt'
    ];

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = \Purifier::clean($value);
    }

    public function searchable()
    {
        static::setSearchableAsUserContext($this->user);

        $this->traitSearchable();
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content
        ];
    }

    public function datasets()
    {
        return $this->belongsToMany(Dataset::class)
            ->withTimestamps()
            ->using(DatasetProtocol::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getExcerptAttribute()
    {
        $text = strip_tags($this->attributes['content']);

        return \str_truncate($text);
    }
}
