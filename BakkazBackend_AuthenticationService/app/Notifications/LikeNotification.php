<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\FirebaseChannel;
use App\Helpers\ResponseHelpers;

class LikeNotification extends Notification
{
    use Queueable;

    protected User $liker;
    protected int $post_id;
    protected string $service;
    protected bool $send_push_notification;

    /**
     * Create a new notification instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(string $service, User $liker, int $post_id, bool $send_push_notification = true)
    {
        $this->service = $service;
        $this->liker = $liker;
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
            'notification' => [
                'title' => 'You have a new reaction!',
                'body' => ucfirst(strtolower($this->liker->name)) . ' has reacted to your post.',
            ],
            'data' => [
                'initiator_id' => $this->liker->id,
                'initiator_name' => $this->liker->name,
                'post_id' => $this->post_id
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
                'initiator' => [
                    'id' => $this->liker->id,
                    'name' => $this->liker->name,
                    'custom_id' => $this->liker->custom_id ?? '@RP' . $this->liker->id,
                ],
                'post' => [
                    'id' => $this->post_id
                ],
                'comment' => null,
                'notification' => [
                    'title' => 'You have a new reaction!',
                    'body' => ucfirst(strtolower($this->liker->name)) . ' has reacted your post.',
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
