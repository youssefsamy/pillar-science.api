<?php

namespace App\Policies;

use App\Models\Dataset;
use App\Models\Protocol;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProtocolPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Protocol $protocol)
    {
        // @todo: Will need to be changed. Check if protocol belongs to a project
        return $user->id === $protocol->user->id;
    }

    public function store(User $user, Dataset $dataset)
    {
        // @todo: Will need to be changed. Check if protocol belongs to a project
        return $user->can('add-protocol', $dataset);
    }

    public function attach(User $user, Protocol $protocol, Dataset $dataset)
    {
        // @todo: Will need to be changed. Check if protocol belongs to a project
        return $user->can('add-protocol', $dataset) && $user->can('view', $protocol);
    }

    public function detach(User $user, Protocol $protocol, Dataset $dataset)
    {
        // @todo: Will need to be changed. Check if protocol belongs to a project
        return $user->can('add-protocol', $dataset) && $user->can('view', $protocol);
    }

    public function update(User $user, Protocol $protocol)
    {
        // @todo: Will need to be changed. Check if protocol belongs to a project
        return $user->id === $protocol->user->id;
    }
}
