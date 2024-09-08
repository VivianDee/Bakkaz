<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\ResponseHelpers;
use App\Channels\FirebaseChannel;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BulkNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $body;
    protected string $service;
    protected bool $send_push_notification;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $service, string $title, string $body, bool $send_push_notification = true)
    {
        $this->service = $service;
        $this->title = $title;
        $this->body = $body;
        $this->send_push_notification = $send_push_notification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->send_push_notification ? [FirebaseChannel::class, 'database'] : ['database'];
    }

    public function toFirebase($notifiable, $tokens = [])
    {
        // if (empty($this->recipients)) {
        //     $tokens = FcmToken::all()->pluck('token')->toArray();
        // } else {
        //     $tokens = FcmToken::whereIn('user_id', $this->recipients)->pluck('token')->toArray();
        // }
        

        return [
            "to" => $tokens,
            "notifiable_id" => $notifiable->id,
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
            ],
            'data' => [],
        ];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        try {

            // Combine the response with additional meta data

            return [
                'initiator' => null,
                'post' => null,
                'comment' => null,
                'notification' => [
                    'title' => $this->title,
                    'body' => $this->body,
                ],
                'service' => $this->service,
            ];
        } catch (\Exception $e) {
            return ResponseHelpers::error(
                message: $e->getMessage()
            );
        }
    }
}
