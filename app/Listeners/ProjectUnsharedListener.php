<?php

namespace App\Listeners;

use App\Events\ProjectUnsharedEvent;
use App\Models\Dataset;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class ProjectUnsharedListener implements ShouldQueue
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
    public function handle(ProjectUnsharedEvent $event)
    {
        /** @var Collection $descendants */
        $descendants = $event->project->directory->descendants;

        $user = $event->user;

        $descendants
            ->filter(function (Dataset $dataset) {
                return $dataset->isDataset();
            })
            ->each(function (Dataset $dataset) use ($user) {
                $dataset->unsearchable($user);
            });
    }
}
