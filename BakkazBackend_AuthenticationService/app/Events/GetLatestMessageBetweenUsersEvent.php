<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GetLatestMessageBetweenUsersEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private ?Message $message  =  null;
    public $auth_user_id;


    /**
     * Create a new event instance.
     */
    public function __construct(Message  $message, mixed $auth_user_id)
    {
        $this->message = $message;
        $this->auth_user_id = $auth_user_id;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("get-latest-message-between-users-channel.{$this->message->recipient_id}.auth-user.{$this->auth_user_id}")
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
