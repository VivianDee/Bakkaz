<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GetLatestChatInChatListEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatList;
    public $userId;
    public $auth_user_id;

    public function __construct(array $chatList, mixed $userId, mixed $auth_user_id)
    {
        $this->chatList = $chatList;
        $this->userId = $userId;
        $this->auth_user_id = $auth_user_id;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('get-latest-message-in-chat-channel.' . $this->userId),
        ];
    }

    public function broadcastWith(): array
    {
        return ['chatList' => $this->chatList];
    }
}
