<?php

namespace App\Api\V1\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;

/**
 * Class DatasetController
 *
 * @package App\Api\V1\Controllers\Project
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Dataset
 */
class DatasetController extends Controller
{
    /**
     * [PROJECT] Show root directory
     *
     * Shows the root directory of the project and the children datasets
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Project $project)
    {
        $this->authorize('view-dataset', $project);

        return response()->json($project->directory);
    }
}
