<?php

namespace App\Observers;

use App\Models\Dataset;
use App\Models\Team;
use App\Models\User;
use TeamTNT\TNTSearch\TNTSearch;

class UserObserver
{
    public function pivotAttaching(User $user, $relationName, $pivotIds, $pivotIdsAttributes)
    {
        if ($relationName === 'teams') {
            /** @var Team[] $teams */
            $teams = Team::findMany($pivotIds);

            foreach ($teams as $team) {
                /** @var Dataset $dir */
                $dir = Dataset::make([
                    'name' => $user->name
                ]);

                $dir->team()->associate($team);
                $dir->owner()->associate($user);
                $dir->save();
            }
        }
    }

    public function created(User $user)
    {
        /** @var TNTSearch $search */
        $search = app(TNTSearch::class);

        $search->createIndex(sprintf('datasets.u%s.index', $user->id));
        $search->createIndex(sprintf('protocols.u%s.index', $user->id));
    }
}