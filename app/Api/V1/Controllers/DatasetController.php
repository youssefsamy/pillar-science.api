<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\DatasetCreateDirectoryRequest;
use App\Api\V1\Requests\DatasetCreateMappingRequest;
use App\Api\V1\Requests\DatasetUploadRequest;
use App\Exceptions\ApiException;
use App\Exceptions\InvalidParameterException;
use App\Exceptions\StorageSyncException;
use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Services\Datasets\DatasetManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

/**
 * Class DatasetController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Dataset
 */
class DatasetController extends Controller
{
    /**
     * Displays a dataset and his children
     *
     * @param Dataset $dataset
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @SWG\Get(
     *     path="/datasets/{dataset}",
     *     summary="Show dataset",
     *     operationId="api.datasets.show",
     *     produces={"application/json"},
     *     tags={"datasets"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Dataset information",
     *         @SWG\Schema(
     *             ref="#/definitions/Dataset"
     *         ),
     *     ),
     * )
     */
    public function show(Dataset $dataset)
    {
        $this->authorize('view', $dataset);

        return response()->json(
            $dataset->load(['children.protocols', 'children.metadata', 'currentVersion.originator', 'versions' => function (HasMany $query) {
                $query->latest();
            }])
        );
    }

    /**
     * Update the details and/or content of the dataset. Any attribute change will create
     * a new version. If the content changes, it will also create a new version.
     *
     * @param Dataset $dataset
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @SWG\Put(
     *     path="/datasets/{dataset}",
     *     summary="Update dataset",
     *     operationId="api.datasets.update",
     *     produces={"application/json"},
     *     tags={"datasets"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="file",
     *         required=false,
     *         type="file",
     *         in="formData"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         required=false,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Dataset information",
     *         @SWG\Schema(
     *             ref="#/definitions/Dataset"
     *         ),
     *     ),
     * )
     */
    public function update(Dataset $dataset, Request $request, DatasetManager $manager)
    {
        $this->authorize('update', $dataset);

        $content = null;

        if ($request->hasFile('file')) {
            $content = $request->file('file');
        } else if ($request->has('content')) {
            $content = $request->get('content');
        }

        if ($content) {
            $this->authorize('upload', $dataset);

            $manager->updateDatasetContent($content, $dataset);
        }

        $dataset->refresh();

        $dataset->update($request->only('name'));

        $dataset->refresh();

        return response()->json($dataset);
    }

    /**
     * @param Dataset $dataset
     * @param Dataset $target
     * @param DatasetManager $manager
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function move(Dataset $dataset, Dataset $target, DatasetManager $manager)
    {
        $this->authorize('move', [$dataset, $target]);

        // Already at target, no change
        if ($dataset->parent->id === $target->id) {
            return response()->json(null, \Dingo\Api\Http\Response::HTTP_NO_CONTENT);
        }

        if (!$target->isDirectory()) {
            throw InvalidParameterException::make(['name' => 'Target dataset must be a directory']);
        }

        $manager->move($dataset, $target);

        return response()->json($dataset);
    }

    /**
     * @param Dataset $dataset
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function tree(Dataset $dataset)
    {
        $this->authorize('view', $dataset);

        $datasets = $dataset
            ->descendants()
            ->where('type', Dataset::TYPE_DIRECTORY)
            ->get()
            ->makeVisible('parent_id');

        return response()->json($datasets);
    }

    public function map(Dataset $dataset, Dataset $target, DatasetManager $manager, DatasetCreateMappingRequest $request)
    {
        $this->authorize('map', [$dataset, $target]);

        $dataset = $manager->createMapping($dataset, $target);

        return response()->json($dataset);
    }

    /**
     * Delete
     *
     * Soft deletes a dataset and his children. Will create a deleted version for each
     * dataset.
     *
     * @param Dataset $dataset
     * @param DatasetManager $manager
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     *
     * @SWG\Delete(
     *     path="/datasets/{dataset}",
     *     summary="Delete a dataset",
     *     operationId="api.datasets.delete",
     *     produces={"application/json"},
     *     tags={"datasets"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Dataset information",
     *         @SWG\Schema(
     *             ref="#/definitions/Dataset"
     *         )
     *     )
     * )
     */
    public function destroy(Dataset $dataset, DatasetManager $manager)
    {
        $this->authorize('destroy', $dataset);

        $manager->delete($dataset);

        return response()->json(null);
    }

    /**
     * Create a new directory dataset into the specified dataset
     *
     * @param Dataset $dataset
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @SWG\Post(
     *     path="/datasets/{dataset}/create-directory",
     *     description="Creates a directory as a children of the specified dataset. The specified dataset needs to be a directory type dataset.",
     *     operationId="api.datasets.createDirectory",
     *     produces={"application/json"},
     *     tags={"datasets"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         description="The dataset directory into which we will create the new dataset directory",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         description="The name of the new directory dataset",
     *         default="New directory",
     *         required=false,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Directory dataset created",
     *         @SWG\Schema(
     *             ref="#/definitions/Metadata"
     *         )
     *     )
     * )
     */
    public function createDirectory(Dataset $dataset, DatasetCreateDirectoryRequest $request, DatasetManager $manager)
    {
        $this->authorize('upload', $dataset);

        // @TODO: Should not be $request->all()?
        $newDataset = $manager->createDirectory($request->all(), $dataset);

        return response()->json($newDataset, Response::HTTP_CREATED);
    }

    /**
     * Upload
     *
     * Uploads a file as a new dataset into the specified dataset (directory) as a child.
     *
     * @param Dataset $dataset
     * @param Request $request
     * @param DatasetManager $manager
     * @throws StorageSyncException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @SWG\Post(
     *     path="/datasets/{dataset}/upload",
     *     summary="Upload a dataset into another one",
     *     operationId="api.datasets.upload",
     *     produces={"application/json"},
     *     tags={"datasets"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="file",
     *         required=false,
     *         type="file",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Dataset information",
     *         @SWG\Schema(
     *             ref="#/definitions/Dataset"
     *         ),
     *     ),
     * )
     */
    public function upload(Dataset $dataset, DatasetUploadRequest $request, DatasetManager $manager)
    {
        $this->authorize('upload', $dataset);

        /** @var UploadedFile $file */
        $file = $request->file('file');
        list($newDataset, $isCreate) = $manager->storeAndCreateUploadedFile($file, $dataset);

        /** @var Dataset $newDataset */
        $newDataset->load(['protocols', 'metadata', 'currentVersion.originator', 'versions' => function (HasMany $query) {
            $query->latest();
        }]);

        return response()->json($newDataset, $isCreate ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    public function preview(Dataset $dataset, Request $request)
    {
        // @todo: Signed url for authorization

        return $dataset->getResponse($request->get('raw', false));
    }

    /**
     * @param Dataset $dataset
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @SWG\Get(
     *     path="/datasets/{dataset}/ancestors",
     *     summary="Update dataset",
     *     operationId="api.datasets.update",
     *     produces={"application/json"},
     *     tags={"datasets"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="file",
     *         required=false,
     *         type="file",
     *         in="formData"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         required=false,
     *         type="string",
     *         in="formData"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Dataset information",
     *         @SWG\Schema(
     *             ref="#/definitions/Dataset"
     *         ),
     *     ),
     * )
     */
    public function ancestors(Dataset $dataset)
    {
        $this->authorize('view', $dataset);

        $datasets = $dataset->ancestors()->with('protocols', 'metadata')->get();

        $datasets = $datasets->add($dataset->load('protocols', 'metadata'));

        $datasets->each(function (Dataset $dataset, int $index) {
            $dataset->order = $index;
        });

        $datasets->makeVisible('order');

        return response()->json($datasets);
    }
}
