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

class FavouritesPostNotification extends Notification
{
    use Queueable;

    protected User $favourite;
    protected int $post_id;
    protected string $body;
    protected string $service;

    /**
     * Create a new notification instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(string $service, User $favourite, int $post_id, string $body = null)
    {
        $this->service = $service;
        $this->favourite = $favourite;
        $this->post_id = $post_id;
        $this->body = $body;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return [FirebaseChannel::class];
    }

    public function toFirebase($notifiable, $tokens = [])
    {

        return [
            "to" => $tokens,
            "notifiable_id" => $notifiable->id,
            "notification" => [
                "title" => ucfirst(strtolower($this->favourite->name)) . " has made a new post.",
                "body" => $this->body,
            ],
            "data" => [
                "initiator_id" => $this->favourite->id,
                "initiator_name" => $this->favourite->name,
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
                    "id" => $this->favourite->id,
                    "name" => $this->favourite->name,
                    "custom_id" =>
                    $this->favourite->custom_id ??
                        "@RP" . $this->favourite->id,
                ],
                "post" => [
                    "id" => $this->post_id,
                ],
                "comment" => null,
                "notification" => [
                    "title" => ucfirst(strtolower($this->favourite->name)) . " has made a new post.",
                    "body" => $this->body,
                ],
                "service" => $this->service,
            ];
        } catch (\Exception $e) {
            Log::debug($e);

            return ResponseHelpers::error(message: $e->getMessage());
        }
    }
}
