<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\Metadata
 *
 * @mixin \Eloquent
 * @SWG\Definition (
 *     type="object",
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         example="1"
 *     ),
 *     @SWG\Property(
 *         property="key",
 *         type="string",
 *         example="color"
 *     ),
 *     @SWG\Property(
 *         property="value",
 *         type="string",
 *         example="red"
 *     ),
 *     @SWG\Property(
 *         property="created_at",
 *         type="string",
 *         example="2018-06-18T16:57:00+0000"
 *     ),
 *     @SWG\Property(
 *         property="updated_at",
 *         type="string",
 *         example="2018-06-18T16:57:00+0000"
 *     )
 * )
 * @property int $id
 * @property int $dataset_id
 * @property string $key
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Dataset $dataset
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Metadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Metadata whereDatasetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Metadata whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Metadata whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Metadata whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Metadata whereValue($value)
 */
class Metadata extends Model implements AuditableInterface
{
    use Auditable;

    protected $fillable = [
        'key',
        'value'
    ];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }
}
