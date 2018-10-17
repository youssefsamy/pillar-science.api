<?php

namespace App\Observers;

use App\Models\Dataset;
use App\Models\Project;
use App\Services\Datasets\DatasetManager;

class ProjectObserver
{
    /**
     * @var DatasetManager
     */
    private $manager;

    public function __construct(DatasetManager $manager)
    {
        $this->manager = $manager;
    }

    public function created(Project $project)
    {
        /** @var Dataset $projectRoot */
        $projectRoot = Dataset::create([
            'name' => $project->name
        ]);

        $projectRoot->project()->associate($project);
        $projectRoot->save();
    }

    /**
     * @param Project $project
     * @return bool
     * @throws \Exception
     */
    public function deleting(Project $project)
    {
        $this->manager->delete($project->directory);

        return true;
    }
}