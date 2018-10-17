<?php

namespace App\Models;

use App\Models\Behaviors\Immutable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\DatasetVersion
 *
 * @property-read \App\Models\Dataset $dataset
 * @mixin \Eloquent
 * @property int $id
 * @property int $dataset_id
 * @property int|null $parent_version_id
 * @property string $name
 * @property string|null $path
 * @property int|null $size
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereDatasetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereParentVersionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereUpdatedAt($value)
 * @property int $deleted
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereDeleted($value)
 * @property int|null $originator_id
 * @property-read \App\Models\User|null $originator
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereOriginatorId($value)
 * @property string|null $mime_type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatasetVersion whereMimeType($value)
 */
class DatasetVersion extends Model implements AuditableInterface
{
    use Auditable;
    use Immutable;
    use SerializesDates;

    const PURGED_DATASET_ATTRIBUTES = ['name', 'path', 'originator_id', 'mime_type'];
    const EXTRACTED_DATASET_ATTRIBUTES = ['name', 'path', 'originator_id', 'size', 'parent_version_id', 'mime_type'];

    protected $attributes = [
        'size' => 0
    ];

    protected $fillable = [
        'name',
        'path',
        'size',
        'parent_version_id',
        'originator_id',
        'mime_type'
    ];

    protected $visible = [
        'id',
        'originator',
        'name',
        'mime_type',
        'created_at'
    ];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function originator()
    {
        return $this->belongsTo(User::class);
    }
}
