<?php
use App\Events\MessageSend;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Laravel\Reverb\Events\MessageReceived;

Broadcast::channel('test-c', function ($user) {
    return true;
});


Broadcast::channel('get-unread-message-count-channel.{user_id}.auth-user.{auth_user_id}', function ($user, $user_id) {
//    return Auth::check() && Auth::id() == $user->id;
    return true;
});


Broadcast::channel('get-latest-message-between-users-channel.{user_id}.auth-user.{auth_user_id}', function ($user) {
    return true;
});


Broadcast::channel('get-latest-message-in-chat-channel.{user_id}', function ($user) {
    return true;
});





