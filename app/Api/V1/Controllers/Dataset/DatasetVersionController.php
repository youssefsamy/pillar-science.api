<?php

namespace App\Api\V1\Controllers\Dataset;

use App\Api\V1\Requests\DatasetUploadRequest;
use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\DatasetVersion;
use App\Services\Datasets\DatasetManager;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Http\UploadedFile;

class DatasetVersionController extends Controller
{
    public function preview(Dataset $dataset, DatasetVersion $version, Request $request)
    {
        // @todo: Signed url for authorization

        return $dataset->getResponse($request->get('raw', false), $version);
    }

    public function store(Dataset $dataset, DatasetUploadRequest $request, DatasetManager $manager)
    {
        $this->authorize('upload', $dataset);

        /** @var UploadedFile $file */
        $file = $request->file('file');
        $manager->updateDatasetContent($file, $dataset);

        $dataset->refresh();

        $version = $dataset->currentVersion;

        return response()->json($version, Response::HTTP_CREATED);
    }
}