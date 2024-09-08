<?php

namespace App\Observers;

use App\Events\GetLatestChatInChatListEvent;
use App\Impl\Services\PreferenceImpl;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message)
    {
        if (Auth::check()) {
            $this->sendLatestChatListEvent($message->sender_id);
            $this->sendLatestChatListEvent($message->recipient_id);
        }
    }

    private function sendLatestChatListEvent($userId)
    {
        $user_id = $userId;

        // Fetch the last message for each conversation
        $lastMessages = Message::where(function ($query) use ($user_id) {
            $query->where('sender_id', $user_id)
                ->orWhere('recipient_id', $user_id);
        })
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) use ($user_id) {
                return $item->sender_id == $user_id ? $item->recipient_id : $item->sender_id;
            });

        // Fetch the corresponding messages and users
        $chatList = [];
        foreach ($lastMessages as $message) {
            // Determine the other user involved in the conversation
            $otherUserId = $message->sender_id == $user_id ? $message->recipient_id : $message->sender_id;

            // Call the external service to check if the user is blocked
            $isBlocked = PreferenceImpl::checkIfBlocked($user_id, $otherUserId);

            // Skip this user if they are blocked
            if ($isBlocked) {
                continue;
            }

            // Fetch the user
            $user = User::find($otherUserId);

            // Calculate unread messages count
            $unreadCount = Message::where(function ($query) use ($user_id, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                    ->where('recipient_id', $user_id)
                    ->where('is_read', 0);
            })->count();

            $chatList[] = [
                'message_id'=> $message->id,
                'user_id' => $otherUserId,
                'last_message' => $message->content,
                'timestamp' => $message->created_at,
                'user' => $user,
                'unread_count' => $unreadCount,
            ];
        }

        event(new GetLatestChatInChatListEvent($chatList, $userId, auth()->id()));
    }

    /**
     * Handle the Message "updated" event.
     */
    public function updated(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "deleted" event.
     */
    public function deleted(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "restored" event.
     */
    public function restored(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "force deleted" event.
     */
    public function forceDeleted(Message $message): void
    {
        //
    }
}
