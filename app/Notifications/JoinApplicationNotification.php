<?php

namespace App\Notifications;

use App\Models\JoinInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class JoinApplicationNotification extends Notification
{
    use Queueable;

    private $invitation;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(JoinInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to Pillar Science')
            ->line('You are receiving this email because you were invited to join Pillar Science. Please take a few seconds to setup your account for the first time.')
            ->action('Activate Account', url(config('app.frontend_url'). sprintf('/join/%s', $this->invitation->token)))
            ->line('If you did not request to join Pillar Science, no further action is required.')
            ->greeting('Welcome!')
            ->salutation('Pillar Science Administrative Team');
    }
}
