<?php

namespace App\Policies;

use App\Models\Dataset;
use App\Models\Metadata;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MetadataPolicy
{
    use HandlesAuthorization;

    public function store(User $user, Dataset $dataset)
    {
        // @todo: Will need to be changed. Check if protocol belongs to a project
        return $user->can('add-metadata', $dataset);
    }

    public function destroy(User $user, Metadata $metadata)
    {
        // @todo: Will need to be changed. Check if protocol belongs to a project
        return $user->can('destroy', $metadata->dataset);
    }
}
