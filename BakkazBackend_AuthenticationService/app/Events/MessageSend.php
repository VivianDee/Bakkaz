<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSend implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $recipientId;
    public $messageContent;
    public $chatRoomId;
    public $randomMessage;

    public function __construct($recipientId = 33, $messageContent =  'Jess is the man', $chatRoomId = null)
    {
        $this->recipientId = $recipientId;
        $this->messageContent = $messageContent;
        $this->chatRoomId = $chatRoomId;
        $this->randomMessage = $this->generateRandomMessage();

    }

    public function broadcastOn()
    {
        return new Channel('test-c');
    }

    public function broadcastWith()
    {
        return [
            'recipient_id' => $this->recipientId,
            'message_content' => $this->messageContent,
            'chat_room_id' => $this->chatRoomId,
            'random_message' => $this->randomMessage,
        ];
    }


    private function generateRandomMessage()
    {
        $messages = [
            "Hello there!",
            "Welcome to the channel!",
            "Nice to see you!",
            "How are you today?",
            "Have a great day!",
            "Enjoy your stay!",
            "What's up?",
            "Stay awesome!",
            "Good vibes only!",
            "You're amazing!"
        ];

        return $messages[array_rand($messages)];
    }
}
