<?php

namespace App\Observers;

use App\Models\DatasetVersion;
use App\Models\Project;
use App\Models\User;

class DatasetVersionObserver
{
    /**
     * @param DatasetVersion $datasetVersion
     * @throws \Exception
     */
    public function saved(DatasetVersion $datasetVersion)
    {
        if ($datasetVersion->dataset->isProjectDescendant()) {
            /** @var Project $project */
            $project = $datasetVersion->dataset->rootAncestor()->project;
            $dataset = $datasetVersion->dataset;

            $project->users->each(function (User $user) use ($dataset) {
                $dataset->searchable($user);
            });
        }
    }
}