<?php

namespace App\Services\Datasets;

use App\Exceptions\StorageSyncException;
use App\Models\Dataset;
use App\Models\DatasetVersion;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class DatasetManager
 *
 * @package App\Services\Datasets
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 */
class DatasetManager
{
    /**
     * Creates a new directory under the directory passed as parameter
     *
     * @param array $attributes The attributes for the new directory (dataset)
     * @param Dataset $dataset Directory into which create the new directory
     * @return Dataset
     */
    public function createDirectory(array $attributes, Dataset $dataset)
    {
        return Dataset::create($attributes, $dataset);
    }

    /**
     * @param UploadedFile $file
     * @param Dataset $parent
     * @return array
     * @throws StorageSyncException
     */
    public function storeAndCreateUploadedFile(UploadedFile $file, Dataset $parent)
    {
        $name = $this->storeUploadedFile($file);

        if (!$name) {
            throw new StorageSyncException();
        }

        return $this->createOrUpdateDataset($file, $name, $parent);
    }

    /**
     * @param UploadedFile $file
     * @param string|null $uploadDir
     * @param string|null $disk
     * @return false|string
     */
    public function storeUploadedFile(UploadedFile $file, string $uploadDir = null, string $disk = null)
    {
        return $file->storeAs(
            $uploadDir ?? config('pillar.storage.datasets.upload_dir'),
            $this->generateName(),
            $disk ?? config('pillar.storage.datasets.disk')
        );
    }

    /**
     * @param $content string|UploadedFile
     * @param string|null $uploadDir
     * @param string|null $disk
     * @return array|bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function storeContent($content, string $uploadDir = null, string $disk = null)
    {
        $name = $this->generateName();
        $path = $uploadDir ?? config('pillar.storage.datasets.upload_dir');
        $disk = $disk ?? config('pillar.storage.datasets.disk');

        if ($content instanceof UploadedFile) {
            $success = \Storage::disk($disk)
                ->putFileAs($path, $content, $name);
        } else {
            $success = \Storage::disk($disk)
                ->put(sprintf('%s/%s', $path, $name), $content);
        }

        if (!$success) {
            return false;
        }

        $size = null;

        if ($content instanceof UploadedFile) {
            $size = $content->getSize();
        } else {
            $size = \Storage::disk($disk)
                ->getSize($path);
        }

        $fullpath = sprintf('%s/%s', $path, $name);

        return [$fullpath, $size];
    }

    /**
     * @param string|UploadedFile $content The new content of the target dataset
     * @param Dataset $dataset The target dataset to which we will create a new
     * version with new content
     * @return bool
     */
    public function updateDatasetContent($content, Dataset $dataset)
    {
        list($path, $size) = $this->storeContent($content);

        if ($content instanceof UploadedFile) {
            $mimeType = $content->getMimeType();
        } else {
            $mimeType = $dataset->mime_type;
        }

        return $dataset->update([
            'path' => $path,
            'size' => $size,
            'mime_type' => $mimeType
        ]);
    }

    /**
     * @param UploadedFile $file
     * @param string $name The name of the file on disk
     * @param Dataset|null $parent The parent directory (Dataset) of the dataset
     * @return array
     */
    public function createOrUpdateDataset(UploadedFile $file, string $name, Dataset $parent = null)
    {
        // Sub query for join
        $currentVersion = DatasetVersion::query()
            ->latest()
            ->limit(1);

        $datasetName = $file->getClientOriginalName();

        $dataset = Dataset::query()
            ->select('datasets.*')
            ->joinSub($currentVersion, 'current_version', function (JoinClause $join) use ($datasetName) {
                $join->on('datasets.id', '=', 'current_version.dataset_id')
                    ->where('current_version.name', $datasetName);
            })
            ->where('parent_id', $parent->id)
            ->first();

        $isCreate = false;
        if (!$dataset) {
            $isCreate = true;
            /** @var Dataset $dataset */
            $dataset = Dataset::make([
                'name' => $datasetName,
            ]);
        }

        $dataset->path = $name;
        $dataset->size = $file->getSize();
        $dataset->mime_type = $file->getMimeType();

        if ($parent && $isCreate) {
            $parent->appendNode($dataset);
        } else {
            $dataset->save();
        }

        return [$dataset, $isCreate];
    }

    public function createMapping(Dataset $source, Dataset $targetParent)
    {
        /** @var Dataset $dataset */
        $dataset = Dataset::make([
            'name' => $source->name
        ]);

        $dataset->mappedDataset()->associate($source);

        $targetParent->appendNode($dataset);

        return $dataset;
    }

    /**
     * Generates a (most likely) unique name for the file
     *
     * @return string
     */
    public function generateName()
    {
        return sprintf('%s_%s', date('Ymd_his'), Str::random(40));
    }

    /**
     * Use sparingly, this operation is costly for the database for
     * directories that has a lot of sub datasets and sub directories
     *
     * @param Dataset $dataset
     * @return bool|null
     * @throws \Exception
     */
    public function delete(Dataset $dataset)
    {
        /** @var Dataset $descendant */
        foreach ($dataset->descendants as $descendant) {
            $descendant->delete();
        }

        return $dataset->delete();
    }

    public function move(Dataset $dataset, Dataset $target)
    {
        return $dataset->parent()->associate($target)->save();
    }
}