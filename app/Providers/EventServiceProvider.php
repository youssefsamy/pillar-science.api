<?php

namespace App\Providers;

use App\Events\ProjectSharedEvent;
use App\Events\ProjectUnsharedEvent;
use App\Events\UserAcceptedInvitationEvent;
use App\Listeners\ProjectSharedListener;
use App\Listeners\ProjectUnsharedListener;
use App\Listeners\UserAcceptedInvitationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserAcceptedInvitationEvent::class => [
            UserAcceptedInvitationListener::class,
        ],
        ProjectSharedEvent::class => [
            ProjectSharedListener::class
        ],
        ProjectUnsharedEvent::class => [
            ProjectUnsharedListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
