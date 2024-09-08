<?php

namespace App\Services;

use App\Enums\BakkazServiceType;
use App\Events\GetLatestChatInChatListEvent;
use App\Events\GetLatestMessageBetweenUsersEvent;
use App\Events\GetUnreadMessageCountEvent;
use App\Events\MessageSend;
use App\Events\MessageSent;
use App\Events\TestEvent;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\PreferenceImpl;
use App\Models\User;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Notifications\MessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatsService
{
    public static function sendMessage(Request $request)
    {
        $recipient_id = $request->recipient_id;
        $chat_room_id = $request->chat_room_id;
        $message_content = $request->message_content;
        $reply_message_content =  $request->reply_message_content;
        $reply_message_sender_id =  $request->reply_message_sender_id;
        $type =  $request->type;


        $service = $request->header("service")
            ? BakkazServiceType::from($request->header("service"))->value
            : "";

        if (!$service) {
            return ResponseHelpers::error(message: "Service Not Found");
        }

        $user_id = $request->user()->id;

        $message = new Message([
            "sender_id" => $user_id,
            "content" => $message_content,
        ]);

        if ($chat_room_id) {
            // For group messages
            $message->chat_room_id = $chat_room_id;
            $message->type = $type;
        } else {
            // For direct messages
            $message->recipient_id = $recipient_id;
            $message->type = $type;
        }

        if ($reply_message_content) {
            // For replies messages
            $message->reply_message_content = $reply_message_content;
            $message->reply_message_sender_id = $reply_message_sender_id;
            $message->type = $type;
        }

        $message->save();

        $send_push_notification = true;

        if ($recipient_id) {
            $notification_settings = PreferenceImpl::getNotificationSettings($recipient_id);

            if (!$notification_settings["direct_messages"]) {
                $send_push_notification = false;
            }
        }

        // Send Message Notification
        $notification = new MessageNotification(
            service: $service,
            sender: $request->user(),
            message: $message_content,
            message_id: $message->id,
            timestamp: $message->created_at,
            ref_type: "chat",
            send_push_notification: $send_push_notification
        );

        User::find($recipient_id)->notify($notification);

        // Count unread messages for the recipient
        $unreadCountRecipient = Message::where('recipient_id', $recipient_id)
            ->where('is_read', 0)
            ->count();

        // Count unread messages for the sender
        $unreadCountSender = Message::where('recipient_id', $user_id)
            ->where('is_read', 0)
            ->count();

        // Broadcast the unread message count for both the sender and the recipient
        event(new GetUnreadMessageCountEvent($recipient_id, $unreadCountRecipient, auth()->id()));
        event(new GetUnreadMessageCountEvent($user_id, $unreadCountSender, auth()->id()));

        // Broadcast the latest message event
        event(new GetLatestMessageBetweenUsersEvent($message, auth()->id()));

        return $message;
    }


    public static function getMessages(Request $request)
    {
        $user_id = $request->user()->id;
        $recipient_id = $request->recipient_id;
        $chat_room_id = $request->chat_room_id;
        $offset = $request->offset;
        $limit = $request->limit;

        $query = Message::query()
            ->where(function ($q) use ($user_id, $chat_room_id, $recipient_id) {
                if ($chat_room_id) {
                    // For group messages
                    $q->where("chat_room_id", $chat_room_id);
                } else {
                    // For direct messages
                    $q->where(function ($q) use ($user_id, $recipient_id) {
                        $q->where("sender_id", $user_id)
                            ->where("recipient_id", $recipient_id)
                            ->orWhere("recipient_id", $user_id)
                            ->where("sender_id", $recipient_id);
                    });
                }
            })
            ->orderBy("created_at", "desc")
            ->limit($limit)
            ->offset($offset);

        return $query->get();
    }

    public static function createChatRoom(array $user_ids, string $name): ChatRoom
    {
        $chatRoom = ChatRoom::create(["name" => $name]);
        $chatRoom->users()->attach($user_ids);

        return $chatRoom;
    }

    public static function getChatRooms(int $user_id): array
    {
        $user = User::find($user_id);
        if (!$user) {
            return [];
        }
        return $user->chatRooms()->with("users")->get()->toArray();
    }

    public static function addUserToChatRoom(int $chat_room_id, int $user_id)
    {
        $chatRoom = ChatRoom::find($chat_room_id);
        if ($chatRoom) {
            $chatRoom->users()->attach($user_id);
        }
    }

    public static function removeUserFromChatRoom(int $chat_room_id, int $user_id)
    {
        $chatRoom = ChatRoom::find($chat_room_id);
        if ($chatRoom) {
            $chatRoom->users()->detach($user_id);
        }
    }

    public static function getChatRoomUsers(int $chat_room_id): array
    {
        $chatRoom = ChatRoom::find($chat_room_id);
        if (!$chatRoom) {
            return [];
        }
        return $chatRoom->users()->get()->toArray();
    }

    public static function markMessageAsRead(int $message_id)
    {
        $message = Message::find($message_id);
        if ($message) {
            $message->is_read = 1;
            $message->save();
        }
    }

    public static function getUnreadMessagesCount(Request $request)
    {
        $user_id = $request->route('user_id');
        $current_user_id = $request->user()->id ?? null;
        if (!$current_user_id) {
            return 0;
        }

        return Message::where(function ($query) use ($user_id, $current_user_id) {
            $query->where('sender_id', $user_id)
                ->where('recipient_id', $current_user_id)
                ->where('is_read', false);
        })->count();
    }

    public static function deleteMessage(int $message_id)
    {
        Message::destroy($message_id);
    }

    public static function deleteChatRoom(int $chat_room_id)
    {
        $chatRoom = ChatRoom::find($chat_room_id);
        if ($chatRoom) {
            $chatRoom->users()->detach();
            $chatRoom->delete();
        }
    }
    public static function getChatList(Request $request)
    {
        $user_id = request()->user()->id ?? null;

        if (!$user_id) {
            return ResponseHelpers::unprocessableEntity("User ID is required");
        }

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

            // Count unread messages for the current user
            $unreadCount = Message::where('recipient_id', $user_id)
                ->where('sender_id', $otherUserId)
                ->where('is_read', 0)
                ->count();

            $chatList[] = [
                'message_id' => $message->id,
                'user_id' => $otherUserId,
                'last_message' => $message->content,
                'timestamp' => $message->created_at,
                'user' => $user,
                'unread_count' => $unreadCount,
            ];
        }

        return ResponseHelpers::success($chatList);
    }
}
