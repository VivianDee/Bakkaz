<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\FirebaseChannel;
use App\Helpers\ResponseHelpers;
use App\Models\User;

class ReplyNotification extends Notification
{
    use Queueable;

    protected User $replier;
    protected int $comment_id;
    protected int $post_id;
    protected string $service;
    protected bool $send_push_notification;

    /**
     * Create a new notification instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(string $service, User $replier, int $post_id, int $comment_id, bool $send_push_notification = true)
    {
        $this->service = $service;
        $this->replier = $replier;
        $this->comment_id = $comment_id;
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
                'title' => 'You have a new reply!',
                'body' => ucfirst(strtolower($this->replier->name)) . ' has replied to your comment.',
            ],
            'data' => [
                'initiator_id' => $this->replier->id,
                'initiator_name' => $this->replier->name,
                'post_id' => $this->post_id,
                'comment_id' => $this->comment_id
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
                    'id' => $this->replier->id,
                    'name' => $this->replier->name,
                    'custom_id' => $this->replier->custom_id ?? '@RP' . $this->replier->id,
                ],
                'post' => [
                    'id' => $this->post_id
                ],
                'comment' => [
                    'id' => $this->comment_id
                ],
                'notification' => [
                    'title' => 'You have a new reply!',
                    'body' => ucfirst(strtolower($this->replier->name)) . ' has replied to your comment.',
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
