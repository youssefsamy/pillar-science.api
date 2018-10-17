<?php

namespace App\Policies;

use App\Models\Dataset;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DatasetPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Dataset $dataset
     * @return bool
     */
    public function view(User $user, Dataset $dataset)
    {
        $root = $dataset->rootAncestor();

        if ($root->isProjectRoot()) {
            return $user->can('view-dataset', $root->project);
        } else if ($root->isRemoteDirectoryRoot()) {
            return $user->can('view-dataset', $root->remoteDirectory);
        }

        return false;
    }

    /**
     * @param User $user
     * @param Dataset $dataset
     * @return bool
     */
    public function update(User $user, Dataset $dataset)
    {
        $root = $dataset->rootAncestor();

        if ($root->isProjectRoot()) {
            return $user->can('update-dataset', $root->project);
        } else if ($root->isRemoteDirectoryRoot()) {
            return $user->can('update-dataset', $root->remoteDirectory);
        }

        return false;
    }

    /**
     * @param User $user
     * @param Dataset $dataset The parent dataset (as directory) to receive the upload
     * @return bool
     */
    public function upload(User $user, Dataset $dataset)
    {
        $root = $dataset->rootAncestor();

        if ($root->isProjectRoot()) {
            return $user->can('upload-dataset', $root->project);
        } else if ($root->isRemoteDirectoryRoot()) {
            return $user->can('upload-dataset', $root->remoteDirectory);
        }

        return false;
    }

    /**
     * @param User $user
     * @param Dataset $dataset
     * @return bool
     */
    public function destroy(User $user, Dataset $dataset)
    {
        $root = $dataset->rootAncestor();

        if ($root->isProjectRoot()) {
            return $user->can('destroy-dataset', $root->project);
        } else if ($root->isRemoteDirectoryRoot()) {
            return $user->can('destroy-dataset', $root->remoteDirectory);
        }

        return false;
    }

    public function addMetadata(User $user, Dataset $dataset)
    {
        $root = $dataset->rootAncestor();

        if ($root->isProjectRoot()) {
            return $user->can('add-metadata-dataset', $root->project);
        } else if ($root->isRemoteDirectoryRoot()) {
            return $user->can('add-metadata-dataset', $root->remoteDirectory);
        }

        return false;
    }

    public function addProtocol(User $user, Dataset $dataset)
    {
        $root = $dataset->rootAncestor();

        if ($root->isProjectRoot()) {
            return $user->can('add-protocol-dataset', $root->project);
        } else if ($root->isRemoteDirectoryRoot()) {
            return $user->can('add-protocol-dataset', $root->remoteDirectory);
        }

        return false;
    }

    public function map(User $user, Dataset $dataset, Dataset $target)
    {
        $sourceRoot = $dataset->rootAncestor();
        $targetRoot = $target->rootAncestor();

        $canMapToSource = false;
        $canMapToTarget = false;

        if ($sourceRoot->isProjectRoot()) {
            $canMapToSource = $user->can('map-dataset', $sourceRoot->project);
        } else if ($sourceRoot->isRemoteDirectoryRoot()) {
            $canMapToSource = $user->can('map-dataset', $sourceRoot->remoteDirectory);
        }

        if ($targetRoot->isProjectRoot()) {
            $canMapToTarget = $user->can('map-dataset', $targetRoot->project);
        } else if ($targetRoot->isRemoteDirectoryRoot()) {
            $canMapToTarget = $user->can('map-dataset', $targetRoot->remoteDirectory);
        }

        return $canMapToSource && $canMapToTarget;
    }

    public function move(User $user, Dataset $dataset, Dataset $targetDataset)
    {
        $sourceRoot = $dataset->rootAncestor();
        $targetRoot = $targetDataset->rootAncestor();

        $canMoveSource = false;
        $canMoveAtTarget = false;

        if ($sourceRoot->isProjectRoot()) {
            $canMoveSource = $user->can('move-dataset', $sourceRoot->project);
        } else if ($sourceRoot->isRemoteDirectoryRoot()) {
            $canMoveSource = $user->can('move-dataset', $sourceRoot->remoteDirectory);
        }

        if ($targetRoot->isProjectRoot()) {
            $canMoveAtTarget = $user->can('move-dataset', $targetRoot->project);
        } else if ($targetRoot->isRemoteDirectoryRoot()) {
            $canMoveAtTarget = $user->can('move-dataset', $targetRoot->remoteDirectory);
        }

        return $canMoveSource && $canMoveAtTarget;
    }
}
