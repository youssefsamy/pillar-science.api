<?php

namespace App\Listeners;

use App\Events\UserAcceptedInvitationEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maknz\Slack\Attachment;
use Maknz\Slack\Client as Slack;

class UserAcceptedInvitationListener implements ShouldQueue
{
    /**
     * @var Slack
     */
    private $slack;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Slack $slack)
    {
        $this->slack = $slack;
    }

    /**
     * Handle the event.
     *
     * @param  UserAcceptedInvitationEvent  $event
     * @return void
     */
    public function handle(UserAcceptedInvitationEvent $event)
    {
        if (!config('slack.enabled')) {
            \Log::info(sprintf('%s slack notification disabled', UserAcceptedInvitationEvent::class));
            return;
        }

        $this->slack->createMessage()
            ->to('#production-v2')
            ->attach(new Attachment([
                'pretext' => 'A user just joined Pillar Science',
                'color' => '#239929',
                'fields' => [
                    [
                        'title' => 'Name',
                        'value' => $event->invitation->user->name ?? '',
                        'short' => false,
                    ] , [
                        'title' => 'Email',
                        'value' => $event->invitation->user->email ?? '',
                        'short' => false,
                    ]
                ]
            ]))
            ->send();
    }
}
