<?php


namespace App\Observers;


use App\Models\Dataset;
use App\Models\RemoteDirectory;

class RemoteDirectoryObserver
{
    public function created(RemoteDirectory $remoteDirectory)
    {
        /** @var Dataset $projectRoot */
        $projectRoot = Dataset::create([
            'name' => $remoteDirectory->name
        ]);

        $projectRoot->remoteDirectory()->associate($remoteDirectory);
        $projectRoot->save();
    }

    public function updated(RemoteDirectory $remoteDirectory)
    {
        if ($remoteDirectory->name !== $remoteDirectory->directory->name) {
            $remoteDirectory->directory->update([
                'name' => $remoteDirectory->name
            ]);
        }
    }
}