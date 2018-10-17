<?php

namespace App\Models;

use App\Models\Behaviors\SearchableByUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Kalnoy\Nestedset\NodeTrait;
use League\Csv\Reader;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * App\Models\Dataset
 *
 * @property int $id
 * @property string $name
 * @property string $vname
 * @property string $type
 * @property int|null $parent_id
 * @property string|null $parent_type
 * @property int|null $owner
 * @property string $path
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $parent|self
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereParentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereVname($value)
 * @mixin \Eloquent
 * @property int $_lft
 * @property int $_rgt
 * @property int|null $team_id
 * @property int|null $owner_id
 * @property-read \Kalnoy\Nestedset\Collection|\App\Models\Dataset[] $children
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset d()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereLft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereRgt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereTeamId($value)
 * @property int|null $project_id
 * @property-read \App\Models\Project|null $project
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereProjectId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset withoutTrashed()
 * @property int $document_id
 * @property int $version
 * @property int|null $size
 * @property-read \App\Models\Dataset|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DatasetVersion[] $versions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereVersion($value)
 * @property-read \App\Models\DatasetVersion $currentVersion
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset ordered()
 * @property-read \Kalnoy\Nestedset\Collection|\App\Models\Dataset[] $traitChildren
 * @property mixed $mime_type
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Metadata[] $metadata
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Protocol[] $protocols
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
 *         example="My dataset name"
 *     ),
 *     @SWG\Property(
 *         property="type",
 *         type="string",
 *         enum={"directory", "dataset"}
 *     ),
 *     @SWG\Property(
 *         property="size",
 *         type="integer",
 *         example="74"
 *     ),
 *     @SWG\Property(
 *         property="mime_type",
 *         type="string",
 *         description="Will always be null for directory type",
 *         example="plain/text"
 *     )
 * )
 * @property int|null $remote_directory_id
 * @property-read \App\Models\RemoteDirectory|null $remoteDirectory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dataset whereRemoteDirectoryId($value)
 */
class Dataset extends Model implements AuditableInterface
{
    use Auditable;
    use NodeTrait {
        children as traitChildren;
    }
    use SerializesDates;
    use SoftDeletes;
    use SearchableByUser {
        searchable as traitSearchable;
        unsearchable as traitUnsearchable;
    }

    public $asYouType = true;

    const TYPE_DIRECTORY = 'directory';
    // Leaf node of filesystem (aka file)
    const TYPE_DATASET = 'dataset';
    const TYPE_SYMLINK = 'symlink';
    const TYPE_UNKNOWN = 'unknown';

    const ROOT_TYPE_PROJECT = 'project';
    const ROOT_TYPE_REMOTE_DIRECTORY = 'remote_directory';
    const ROOT_TYPE_TEAM_PERSONAL_ROOT = 'team_personal';
    const ROOT_TYPE_TEAM_ROOT = 'team';
    const ROOT_TYPE_UNKNOWN = 'unknown';

    protected $attributes = [
        'size' => 0
    ];

    protected $fillable = [
        'name',
        'size',
        'path',
        'type',
        'mime_type'
    ];

    protected $visible = [
        'id',
        'name',
        'mime_type',
        'type',
        'size',
        'children',
        'created_at',
        'updated_at',
        'protocols',
        'metadata',
        'currentVersion',
        'versions',
        'author',
        'parent_id',
        'mapped_dataset_id',
        'root_type',
        'inherits_metadata',
        'inherits_protocols'
    ];

    protected $appends = [
        'name',
        'mime_type',
        'author',
        'root_type',
        'inherits_metadata',
        'inherits_protocols'
    ];

    /**
     * If team_id is not null and team_id is null, this represents the
     * shared directory of a team. If both team_id and owner_id are not
     * null, it represents the user's personal root directory in a team
     * See owner() for more information
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * If this value is not null, it represents the root personal directory
     * of a user within a team (specified by team_id).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * If this value is not null, it represents the root personal directory
     * of a user within a team (specified by team_id).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function remoteDirectory()
    {
        return $this->belongsTo(RemoteDirectory::class);
    }

    public function isDirectory()
    {
        return $this->type === self::TYPE_DIRECTORY;
    }

    public function isDataset()
    {
        return $this->type === self::TYPE_DATASET;
    }

    /**
     * Returns the top most ancestor of the current
     * dataset. If already top most, return self.
     *
     * @return self
     */
    public function rootAncestor()
    {
        if ($this->ancestors()->exists()) {
            return $this->ancestors()->first();
        }

        return $this;
    }

    public function getRootTypeAttribute()
    {
        $root = $this->rootAncestor();

        if ($root->isProjectRoot()) {
            return self::ROOT_TYPE_PROJECT;
        } else if ($root->isRemoteDirectoryRoot()) {
            return self::ROOT_TYPE_REMOTE_DIRECTORY;
        } else if ($root->isTeamPersonalRoot()) {
            return self::ROOT_TYPE_TEAM_PERSONAL_ROOT;
        } else if ($root->isTeamRoot()) {
            return self::ROOT_TYPE_TEAM_ROOT;
        }

        return self::ROOT_TYPE_UNKNOWN;
    }

    /**
     * Whether the directory represents the root directory of a
     * project.
     *
     * @return bool
     */
    public function isProjectRoot()
    {
        return ($this->project_id !== null);
    }

    /**
     * Whether the dataset is any node in a tree that belongs to a project
     *
     * @return bool
     */
    public function isProjectDescendant()
    {
        return $this->rootAncestor()->isProjectRoot();
    }

    /**
     * Whether the directory represent the root public directory of
     * a team. This is not bound to a specific user.
     *
     * @return bool
     */
    public function isTeamRoot()
    {
        return ($this->team_id !== null && $this->owner_id === null);
    }

    /**
     * Whether the directory represent the root directory of a member
     * of a team. This is an individual user directory (per team and per user)
     *
     * @return bool
     */
    public function isTeamPersonalRoot()
    {
        return ($this->team_id !== null && $this->owner_id !== null);
    }

    public function isRemoteDirectoryRoot()
    {
        return ($this->remote_directory_id !== null);
    }

    public function getNameAttribute()
    {
        return $this->currentVersion->name;
    }

    public function getPathAttribute()
    {
        return $this->currentVersion->path;
    }

    public function getMimeTypeAttribute()
    {
        return $this->currentVersion->mime_type;
    }

    public function children()
    {
        return $this->traitChildren()
            ->orderBy('type', 'desc');
    }

    /**
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function currentVersion()
    {
        return $this->hasOne(DatasetVersion::class)
            ->latest()
            ->orderBy('id', 'desc');
    }

    /**
     * First version ever published of this dataset
     *
     * @return $this
     */
    public function firstVersion()
    {
        return $this->hasOne(DatasetVersion::class)
            ->oldest()
            ->orderBy('id', 'asc');
    }

    public function mappedDataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    /**
     * Original author of the dataset
     */
    public function getAuthorAttribute()
    {
        return $this->firstVersion->originator;
    }

    public function versions()
    {
        return $this->hasMany(DatasetVersion::class);
    }

    public function protocols()
    {
        return $this->belongsToMany(Protocol::class)
            ->withTimestamps()
            ->using(DatasetProtocol::class);
    }

    public function metadata()
    {
        return $this->hasMany(Metadata::class);
    }

    /**
     * Returns true if there is at least one metadata on any dataset in the
     * ancestry line of this dataset (including itself)
     */
    public function getInheritsMetadataAttribute() : bool
    {
        $ancestorIds = $this->ancestors->map(function (Dataset $dataset) {
            return $dataset->id;
        });

        $ancestorIds[] = $this->id;

        return Metadata::whereIn('dataset_id', $ancestorIds)->exists();
    }

    public function getInheritsProtocolsAttribute() : bool
    {
        $ancestorIds = $this->ancestors->map(function (Dataset $dataset) {
            return $dataset->id;
        });

        $ancestorIds[] = $this->id;

        return DatasetProtocol::query()
            ->whereIn('dataset_id', $ancestorIds)
            ->exists();
    }

    public function getAncestorsWithProtocols()
    {
        // @todo: Not optimal
        $datasets = $this->ancestors()->get();

        $datasets = $datasets->add($this)->reverse();

        return $datasets->load('protocols');
    }

    public function getContent(DatasetVersion $version = null)
    {
        $path = $this->path;

        if ($version) {
            $path = $version->path;
        }

        return \Storage::disk(config('pillar.storage.datasets.disk'))->get($path);
    }

    public function getResponse($raw = false, DatasetVersion $version = null)
    {
        $version = $version ?? $this->currentVersion;

        if ($this->isCsv() && !$raw) {
            return \Response::json([
                'content' => $this->toHandsontable($version)
            ]);
        }

        $response = \Response::make($this->getContent($version));

        if ($this->mime_type) {
            $response->header('Content-Type', $this->mime_type);
        }

        $response->header('Content-Disposition', sprintf('inline; filename="%s"', $this->name));

        return $response;
    }

    public function isCsv()
    {
        return in_array($this->mime_type, ['text/csv','text/tab-separated-values']);
    }

    public function toHandsontable(DatasetVersion $version)
    {
        return Reader::createFromString($this->getContent($version))
            ->jsonSerialize();
    }

    public function searchable(User $userContext)
    {
        $root = $this->rootAncestor();

        if ($root->isProjectRoot()) {
            static::setSearchableAsUserContext($userContext);

            $this->traitSearchable();
        }
    }

    public function unsearchable(User $userContext)
    {
        if ($this->isProjectDescendant()) {
            static::setSearchableAsUserContext($userContext);

            $this->traitUnsearchable();
        }
    }

    public function toSearchableArray()
    {
        $indexed = [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'mime_type' => $this->mime_type
        ];

        $this->metadata->each(function (Metadata $metadata, $index) use (&$indexed) {
            $key = sprintf('metadata_%s_key', $index);
            $value = sprintf('metadata_%s_value', $index);

            $indexed[$key] = $metadata->key;
            $indexed[$value] = $metadata->value;
        });

        return $indexed;
    }

    /**
     * This acts as a proxy to create DatasetVersion through Dataset normal operations
     *
     * This method intercepts Dataset attributes that are specified in
     * DatasetVersion::EXTRACTED_DATASET_ATTRIBUTES (which are invalid Dataset attributes) and
     * creates a new DatasetVersion for that dataset from those attributes. The extracted attributes
     * are purged from the original Dataset before being saved.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $exists = $this->exists;

        if ($this->attributes['path'] ?? false) {
            $type = Dataset::TYPE_DATASET;
        } else if ($this->attributes['mapped_dataset_id'] ?? false) {
            $type = Dataset::TYPE_SYMLINK;
        } else {
            $type = Dataset::TYPE_DIRECTORY;
        }

        $this->attributes['type'] = $this->attributes['type'] ?? $type;

        $collect = collect($this->attributes);
        $this->attributes = $collect->except(DatasetVersion::PURGED_DATASET_ATTRIBUTES)->toArray();

        /** @var Dataset $dataset */
        $saved = parent::save($options);

        // Something went wrong, do not continue with DatasetVersion creation
        if (!$saved) {
            return false;
        }

        $newVAttributes = $collect->only(DatasetVersion::EXTRACTED_DATASET_ATTRIBUTES)->toArray();

        if ($this->parent) {
            $newVAttributes['parent_version_id'] = $this->parent->currentVersion->id;
        }

        // If we are updating the Dataset
        if ($exists) {
            $currentVersion = $this->currentVersion;
            $current = $currentVersion->only(DatasetVersion::EXTRACTED_DATASET_ATTRIBUTES);
            $changes = array_diff_assoc($newVAttributes, $current);

            // If there are not any meaningful changes, do not
            // create a new version
            if (count($changes) === 0) {
                return true;
            }

            $newVersion = $currentVersion->replicate();
            $newVersion->fill($newVAttributes)
                ->save();

            return true;
        }

        $user = \Auth::user();

        // originator_id defaults to the current user
        $newVAttributes = array_merge([
            'originator_id' => $user ? $user->id : null
        ], $newVAttributes);

        // If we are creating the Dataset
        DatasetVersion::make($newVAttributes)
            ->dataset()
            ->associate($this)
            ->save();

        return true;
    }
}
