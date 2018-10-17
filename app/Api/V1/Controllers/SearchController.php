<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Search\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * @param Request $request
     * @param SearchService $searchService
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/search",
     *     summary="Search through the current user's resources",
     *     description="Search through a user's Datasets, Protocols and Metadata",
     *     operationId="api.search",
     *     produces={"application/json"},
     *     tags={"search"},
     *     @SWG\Parameter(
     *         name="query",
     *         required=true,
     *         type="string",
     *         in="query"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Search result",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(
     *                 @SWG\Property(
     *                     property="resource",
     *                     type="object",
     *                     example="Dataset Model, Protocol Model, etc"
     *                 ),
     *                 @SWG\Property(
     *                     property="type",
     *                     type="string",
     *                     enum={"Dataset"},
     *                     example="Dataset | Protocol | Metadata ..."
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function search(Request $request, SearchService $searchService)
    {
        $query = $request->get('query');
        $limit = $request->get('limit', null);

        $results = $searchService->search($query, \Auth::user(), $limit);

        return response()->json($results);
    }
}