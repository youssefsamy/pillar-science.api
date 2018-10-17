<?php

namespace App\Observers;

use App\Models\Dataset;
use App\Models\DatasetVersion;

class DatasetObserver
{
    public function deleting(Dataset $dataset)
    {
        /** @var DatasetVersion $version */
        $version = $dataset->currentVersion->replicate();

        $version->deleted = true;

        $dataset->versions()->save($version);

        return true;
    }
}