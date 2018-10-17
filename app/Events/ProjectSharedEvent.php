<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectSharedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Project
     */
    public $project;
    /**
     * @var User
     */
    public $user;
    /**
     * @var string
     */
    public $role;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Project $project, User $user, string $role)
    {
        $this->project = $project;
        $this->user = $user;
        $this->role = $role;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
