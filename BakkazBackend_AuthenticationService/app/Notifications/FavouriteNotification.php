<?php

namespace App\Notifications;

use App\Helpers\ResponseHelpers;
use Illuminate\Bus\Queueable;
use App\Channels\FirebaseChannel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class FavouriteNotification extends Notification
{

    use Queueable;

    protected User $favouriter;
    protected string $service;
    protected bool $status;
    protected bool $send_push_notification;

    /**
     * Create a new notification instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(string $service, User $favouriter, bool $status, bool $send_push_notification = true)
    {
        $this->service = $service;
        $this->favouriter = $favouriter;
        $this->status = $status;
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
                'title' => 'You have a new favourite!',
                'body' => ucfirst(strtolower($this->favouriter->name)) . ' added you as favorite.',
            ],
            'data' => [
                    'initiator_id' => $this->favouriter->id,
                    'initiator_name' => $this->favouriter->name,
                    'mutual_favourite' => $this->status,
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
                    'id' => $this->favouriter->id,
                    'name' => $this->favouriter->name,
                    'custom_id' => $this->favouriter->custom_id ?? '@RP' . $this->favouriter->id,
                    'mutual_favourite' => $this->status,
                ],
                'post' => null,
                'comment' => null,
                'notification' => [
                    'title' => 'You have a new favourite!',
                    'body' => ucfirst(strtolower($this->favouriter->name)) . ' added you as favorite.',
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
