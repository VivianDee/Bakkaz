<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GetUnreadMessageCountEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $recipientId;
    public int $unreadCount;
    public $auth_user_id;



    public function __construct(int $recipientId, int $unreadCount, mixed $auth_user_id)
    {
        $this->recipientId = $recipientId;
        $this->unreadCount = $unreadCount;
        $this->auth_user_id = $auth_user_id;

    }

    public function broadcastOn(): Channel
    {
        return new Channel("get-unread-message-count-channel.{$this->recipientId}.auth-user.{$this->auth_user_id}");
    }

    public function broadcastWith(): array
    {
        return ['messageCount' => $this->unreadCount];
    }
}
