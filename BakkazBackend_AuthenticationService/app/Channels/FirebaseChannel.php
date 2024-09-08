<?php

namespace App\Channels;

use App\Models\FcmToken;
use Illuminate\Notifications\Notification;
use App\Services\PushNotificationService;

class FirebaseChannel
{
    protected $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFirebase')) {
            return;
        }

        $tokens = $this->getFcmTokens($notifiable->id);
        

        $data = $notification->toFirebase(notifiable: $notifiable, tokens: $tokens);

        $this->pushNotificationService->sendPushNotification($data);
    }

    protected function getFcmTokens($userId)
    {
        return FcmToken::where('user_id', $userId)->pluck('token')->toArray();
    }
}
