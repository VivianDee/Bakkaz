<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        "sender_id", "recipient_id",
        "chat_room_id", "content", "is_read",
        "reply_message_content","reply_message_sender_id",
        "type"
    ];


    public function sender()
    {
        return $this->belongsTo(User::class, "sender_id");
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, "recipient_id");
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, "chat_room_id");
    }

}
