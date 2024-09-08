<?php

namespace App\Notifications;

use App\Channels\FirebaseChannel;
use App\Helpers\ResponseHelpers;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class MessageNotification extends Notification
{
    use Queueable;

    protected User $sender;
    protected string $service;
    protected string $message;
    protected int $message_id;
    protected string $timestamp;
    protected string $ref_type;
    protected bool $send_push_notification;

    /**
     * Create a new notification instance.
     *
     * @param string $service
     * @param User $sender
     * @param int $conversation_id
     * @return void
     */
    public function __construct(string $service, User $sender, string $message, int $message_id, string $timestamp, string $ref_type="chat", bool $send_push_notification = true)
    {
        $this->service = $service;
        $this->sender = $sender;
        $this->message = $message;
        $this->message_id = $message_id;
        $this->timestamp = $timestamp;
        $this->ref_type = $ref_type;
        $this->send_push_notification = $send_push_notification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return $this->send_push_notification ? [FirebaseChannel::class, 'database'] : ['database'];
    }

    /**
     * Get the Firebase representation of the notification.
     *
     * @param mixed $notifiable
     * @param array $tokens
     * @return array
     */
    public function toFirebase($notifiable, $tokens = [])
    {
        return [
            "to" => $tokens,
            "notifiable_id" => $notifiable->id,
            "notification" => [
                "title" => ucfirst(strtolower($this->sender->name)) . " has sent you a message.",
                "body" => $this->message,
            ],
            "data" => [
                "initiator_id" => $this->sender->id,
                "initiator_name" => $this->sender->name,
                "initiator_first_name" => $this->sender->first_name,
                "initiator_last_name" => $this->sender->last_name,
                "initiator_email" => $this->sender->email,
                "message" => $this->message,
                "a_message_id" => $this->message_id,
                "timestamp" => $this->timestamp,
                "ref_type" => $this->ref_type,
            ],
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
            return [
                "initiator" => [
                    "id" => $this->sender->id,
                    "name" => $this->sender->name,
                    'custom_id' => $this->sender->custom_id ?? '@RP' . $this->sender->id,
                    "first_name" => $this->sender->first_name,
                    "last_name" => $this->sender->last_name,
                    "email" => $this->sender->email,
                    "message" => $this->message,
                    "message_id" => $this->message_id,
                    "timestamp" => $this->timestamp,
                    "ref_type" => $this->ref_type,
                ],
                "notification" => [
                    "title" => "New Message",
                    "body" => ucfirst(strtolower($this->sender->name)) . "~ " . $this->message,
                ],
                "service" => $this->service,
            ];
        } catch (\Exception $e) {
            Log::debug($e);

            return ResponseHelpers::error(message: $e->getMessage());
        }
    }
}
