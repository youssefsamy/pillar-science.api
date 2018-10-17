<?php

namespace App\Api\V1\Controllers\Dataset;

use App\Api\V1\Requests\MetadataCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\Metadata;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetadataController extends Controller
{
    /**
     * List all metadata key-pair for a dataset
     *
     * @param Dataset $dataset
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/datasets/{dataset}/metadata",
     *     operationId="api.datasets.metadata.index",
     *     produces={"application/json"},
     *     tags={"metadata"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         description="The dataset from which we want to see the metadata",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="List of metadata key-value pairs",
     *         @SWG\Schema(
     *             @SWG\Items(
     *                 ref="#/definitions/Metadata"
     *             )
     *         )
     *     )
     * )
     */
    public function index(Dataset $dataset)
    {
        $this->authorize('view', $dataset);

        return response()->json($dataset->metadata);
    }

    /**
     * Stores a new key-value pair metadata for a dataset
     *
     * @param Dataset $dataset
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/datasets/{dataset}/metadata",
     *     operationId="api.datasets.metadata.store",
     *     produces={"application/json"},
     *     tags={"metadata"},
     *     @SWG\Parameter(
     *         name="dataset",
     *         description="The dataset to which we will attach the metadata",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Metadata key-value pair added to dataset",
     *         @SWG\Schema(
     *             ref="#/definitions/Metadata"
     *         )
     *     )
     * )
     */
    public function store(Dataset $dataset, MetadataCreateRequest $request)
    {
        $this->authorize('store', [Metadata::class, $dataset]);

        $metadata = Metadata::make($request->all());
        
        $metadata->dataset()->associate($dataset);
        $metadata->save();
        
        return response()->json($metadata, Response::HTTP_CREATED);
    }
}