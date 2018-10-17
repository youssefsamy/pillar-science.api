<?php

namespace App\Observers;

use App\Models\Metadata;
use App\Models\User;

class MetadataObserver
{
    /**
     * @param Metadata $metadata
     * @throws \Exception
     */
    public function saved(Metadata $metadata)
    {
        if ($metadata->dataset->isProjectDescendant()) {
            $dataset = $metadata->dataset;
            $dataset->rootAncestor()->project->users->each(function (User $user) use ($dataset) {
                $dataset->searchable($user);
            });
        }
    }
}