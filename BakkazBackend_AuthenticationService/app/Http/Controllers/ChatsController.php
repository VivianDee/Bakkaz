<?php

namespace App\Http\Controllers;

use App\Events\MessageSend;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\PreferenceImpl;
use App\Services\ChatsService;
use Illuminate\Http\Request;

class ChatsController extends Controller
{
    public function sendMessage(Request $request)
    {

        $message = ChatsService::sendMessage($request);

        return ResponseHelpers::success($message);
    }

    public function getMessages(Request $request)
    {
        $messages = ChatsService::getMessages($request);

        return ResponseHelpers::success($messages);
    }

    public function createChatRoom(Request $request)
    {
        $validated = $request->validate([
            "user_ids" => "required|array",
            "name" => "required|string",
        ]);

        $chatRoom = ChatsService::createChatRoom(
            $validated["user_ids"],
            $validated["name"]
        );

        return ResponseHelpers::created($chatRoom);
    }

    public function getChatRooms(Request $request)
    {
        $validated = $request->validate([
            "user_id" => "required|integer",
        ]);

        $chatRooms = ChatsService::getChatRooms($validated["user_id"]);

        return ResponseHelpers::success($chatRooms);
    }

    public function addUserToChatRoom(Request $request)
    {
        $validated = $request->validate([
            "chat_room_id" => "required|integer",
            "user_id" => "required|integer",
        ]);

        ChatsService::addUserToChatRoom(
            $validated["chat_room_id"],
            $validated["user_id"]
        );

        return ResponseHelpers::created("User added to chat room");
    }

    public function removeUserFromChatRoom(Request $request)
    {
        $validated = $request->validate([
            "chat_room_id" => "required|integer",
            "user_id" => "required|integer",
        ]);

        ChatsService::removeUserFromChatRoom(
            $validated["chat_room_id"],
            $validated["user_id"]
        );

        return ResponseHelpers::success(message: "User removed from chat room");
    }

    public function getChatRoomUsers(Request $request)
    {
        $validated = $request->validate([
            "chat_room_id" => "required|integer",
        ]);

        $users = ChatsService::getChatRoomUsers($validated["chat_room_id"]);

        return ResponseHelpers::success($users);
    }

    public function markMessageAsRead(Request $request)
    {
        $validated = $request->validate([
            "message_id" => "required|integer",
        ]);

        ChatsService::markMessageAsRead($validated["message_id"]);

        return ResponseHelpers::created("Message marked as read");
    }

    public function getUnreadMessagesCount(Request $request)
    {

        $unreadCount = ChatsService::getUnreadMessagesCount(
            $request
        );

        return $unreadCount;
    }

    public function deleteMessage(Request $request)
    {
        $validated = $request->validate([
            "message_id" => "required|integer",
        ]);

        ChatsService::deleteMessage($validated["message_id"]);

        return ResponseHelpers::success(message: "Message deleted");
    }

    public function deleteChatRoom(Request $request)
    {
        $validated = $request->validate([
            "chat_room_id" => "required|integer",
        ]);

        ChatsService::deleteChatRoom($validated["chat_room_id"]);

        return ResponseHelpers::success(message: "Chat room deleted");
    }

    public function getChatList(Request $request)
    {
       return  ChatsService::getChatList($request);

    }
}
