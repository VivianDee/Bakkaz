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

class MentionNotification extends Notification
{
    use Queueable;

    protected User $mentioned;
    protected int $post_id;
    protected string $service;
    protected bool $send_push_notification;

    /**
     * Create a new notification instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(string $service, User $mentioned, int $post_id, bool $send_push_notification = true)
    {
        $this->service = $service;
        $this->mentioned = $mentioned;
        $this->post_id = $post_id;
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

    public function toFirebase($notifiable, $tokens = [])
    {

        return [
            "to" => $tokens,
            "notifiable_id" => $notifiable->id,
            "notification" => [
                "title" => "You were mentioned in a post!",
                "body" =>
                ucfirst(strtolower($this->mentioned->name)) .
                    " mentioned you in a post.",
            ],
            "data" => [
                "initiator_id" => $this->mentioned->id,
                "initiator_name" => $this->mentioned->name,
                "post_id" => $this->post_id,
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
            // Combine the response with additional meta data

            return [
                "initiator" => [
                    "id" => $this->mentioned->id,
                    "name" => $this->mentioned->name,
                    "custom_id" =>
                    $this->mentioned->custom_id ??
                        "@RP" . $this->mentioned->id,
                ],
                "post" => [
                    "id" => $this->post_id,
                ],
                "comment" => null,
                "notification" => [
                    "title" => "You were mentioned in a post!",
                    "body" =>
                    ucfirst(strtolower($this->mentioned->name)) .
                        " mentioned you in a post.",
                ],
                "service" => $this->service,
            ];
        } catch (\Exception $e) {
            Log::debug($e);

            return ResponseHelpers::error(message: $e->getMessage());
        }
    }
}
