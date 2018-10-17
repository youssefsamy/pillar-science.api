<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Metadata;

class MetadataController extends Controller
{
    /**
     * @param Metadata $metadata
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @SWG\Delete(
     *     path="/metadata/{metadata}",
     *     summary="Delete a metadata key-value pair",
     *     operationId="api.metadata.delete",
     *     produces={"application/json"},
     *     tags={"metadata"},
     *     @SWG\Parameter(
     *         name="metadata",
     *         description="The metadata id",
     *         required=true,
     *         type="integer",
     *         in="path"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Metadata deleted"
     *     )
     * )
     */
    public function destroy(Metadata $metadata)
    {
        $this->authorize('destroy', $metadata);

        $metadata->delete();

        return response()->json($metadata);
    }
}