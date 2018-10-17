<?php

namespace App\Listeners;

use App\Events\ProjectSharedEvent;
use App\Models\Dataset;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class ProjectSharedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ProjectSharedEvent $event)
    {
        /** @var Collection $descendants */
        $descendants = $event->project->directory->descendants;

        /** @var Collection $users */
        $users = $event->project->users;

        $descendants
            ->filter(function (Dataset $dataset) {
                return $dataset->isDataset();
            })
            ->each(function (Dataset $dataset) use ($users) {
                $users->each(function (User $user) use ($dataset) {
                    $dataset->searchable($user);
                });
            });
    }
}
