<?php

namespace App\Observers;

use App\Models\Dataset;
use App\Models\Team;
use App\Models\User;
use App\Services\Datasets\DatasetManager;

class TeamObserver
{
    /**
     * @var DatasetManager
     */
    private $manager;

    public function __construct(DatasetManager $manager)
    {
        $this->manager = $manager;
    }

    public function created(Team $team)
    {
        /** @var Dataset $teamRoot */
        $teamRoot = Dataset::make([
            'name' => $team->name,
        ]);

        $teamRoot->team()->associate($team);
        $teamRoot->save();
    }

    /**
     * @param Team $team
     * @return bool
     * @throws \Exception
     */
    public function deleting(Team $team)
    {
        $this->manager->delete($team->directory);

        $team->userDirectories->map(function (Dataset $dir) {
            $this->manager->delete($dir);
        });

        return true;
    }

    public function pivotAttaching(Team $team, $relationName, $pivotIds, $pivotIdsAttributes)
    {
        if ($relationName === 'members') {
            /** @var User[] $users */
            $users = User::findMany($pivotIds);

            foreach ($users as $user) {
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
}