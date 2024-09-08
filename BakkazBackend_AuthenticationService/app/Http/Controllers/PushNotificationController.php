<?php

namespace App\Http\Controllers;

use App\Enums\BakkazServiceType;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\PreferenceImpl;
use App\Models\FcmToken;
use App\Models\Notification as ModelsNotification;
use App\Models\User;
use App\Notifications\BulkNotification;
use App\Notifications\CommentNotification;
use App\Notifications\FavouriteNotification;
use App\Notifications\FavouritesPostNotification;
use App\Notifications\GeneralNotification;
use App\Notifications\LikeNotification;
use App\Notifications\MessageNotification;
use App\Notifications\MentionNotification;
use App\Notifications\ReplyNotification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;

class PushNotificationController extends Controller
{
    // Notifications
    public function showNotifications(Request $request)
    {
        return PushNotificationService::showNotifications($request);
    }

    public static function updateMutualFavourites(Request $request)
    {
        return PushNotificationService::updateMutualFavourites($request);
    }

    public function sendPushNotification(Request $request)
    {
        try {
            // Define a map of notification types to their corresponding classes
            $notification_classes = [
                "favourite" => FavouriteNotification::class,
                "like" => LikeNotification::class,
                "comment" => CommentNotification::class,
                "favourites_posts" => FavouritesPostNotification::class,
                "mention" => MentionNotification::class,
                "reply" => ReplyNotification::class,
                "general" => GeneralNotification::class,
                "bulk" => GeneralNotification::class,
            ];

            // Basic validation
            $validator = Validator::make($request->all(), [
                "notification_type" =>
                "required|in:" .
                    implode(",", array_keys($notification_classes)),
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            // Additional validation based on notification type
            $notification_type = $request->input("notification_type");
            $additional_rules = [];



            switch ($notification_type) {
                case "favourite":
                    $additional_rules = [
                        "recipient_id" => "required|integer",
                        "initiator_id" => "required|integer",
                        "status" => "required|boolean",
                    ];
                    break;
                case "like":
                case "comment":
                case "mention":
                    $additional_rules = [
                        "recipient_id" => "required|integer",
                        "initiator_id" => "required|integer",
                        "post_id" => "required|integer",
                    ];
                    break;
                case "reply":
                    $additional_rules = [
                        "recipient_id" => "required|integer",
                        "initiator_id" => "required|integer",
                        "post_id" => "required|integer",
                        "comment_id" => "required|integer",
                    ];
                    break;
                case "general":
                    $additional_rules = [
                        "recipient_id" => "required|integer",
                        "title" => "required|string",
                        "body" => "required|string",
                    ];
                    break;
                case "bulk":
                    $additional_rules = [
                        "title" => "required|string",
                        "body" => "required|string",
                        "recipients" => "nullable|array",
                        "send_to_guests" => "nullable|boolean"
                    ];
                    break;
                case "favourites_posts":
                    $additional_rules = [
                        "recipients" => "nullable|array",
                        "initiator_id" => "required|integer",
                        "post_id" => "required|integer",
                        "body" => "required|string",
                    ];
                    break;
            }

            $validator->addRules($additional_rules);
            throw_if($validator->fails(), new ValidationException($validator));

            // Get the user and initiator information
            $recipient_id = $request->input("recipient_id");
            $user = $recipient_id ? User::find($recipient_id) : null;

            $initiator_id = $request->input("initiator_id");
            $initiator = $initiator_id ? User::find($initiator_id) : null;


            if (($initiator_id === $recipient_id) && $notification_type !== "bulk") {
                return ResponseHelpers::error(message: "You cannot send a notification to yourself.");
            }

            // Get the post ID and comment ID if provided
            $post_id = $request->input("post_id");
            $comment_id = $request->input("comment_id");
            $title = $request->input("title");
            $body = $request->input("body");
            $status = $request->input("status");
            $recipients = $request->input("recipients", []);
            // $message = $request->input("message");

            $send_to_guests = $request->input("send_to_guests", false);

            $service =
                $request->header("service") ??
                BakkazServiceType::from($request->header("service"))->value;

            $send_push_notification = true;

            if ($recipient_id) {
                $notification_settings = PreferenceImpl::getNotificationSettings($recipient_id);

                $notification_types = array(
                    "favourite" => "new_favourite",
                    "like" => "likes",
                    "comment" => "post_comments",
                    "reply" => "post_replies",
                    "general" => "general_notifications",
                    // "favourites_posts" => "favourites_posts"
                );

                if (isset($notification_types[$notification_type]) && !$notification_settings[$notification_types[$notification_type]]) {
                    $send_push_notification = false;
                }
            }

            if ($notification_type === "bulk") {
                $send_push_notification = false;
            }

            // Dynamically instantiate the notification class
            $notification_class = $notification_classes[$notification_type];

            $argsMap = [
                "general" => [$service, $title, $body, $send_push_notification],
                "favourite" => [$service, $initiator, $status, $send_push_notification],
                "like" => [$service, $initiator, $post_id, $send_push_notification],
                "comment" => [$service, $initiator, $post_id, $send_push_notification],
                "mention" => [$service, $initiator, $post_id, $send_push_notification],
                "bulk" => [$service, $title, $body, $send_push_notification],
                "reply" => [$service, $initiator, $post_id, $comment_id, $send_push_notification],
                "favourites_posts" => [$service, $initiator, $post_id, $body],
                "default" => [$service, $initiator, $post_id, $comment_id, $send_push_notification]
            ];

            $args = $argsMap[$notification_type] ?? $argsMap["default"];
            $notification = new $notification_class(...$args);

            if ($notification_type === "bulk" || $notification_type === "favourites_posts") {

                $send_push_notification = false;

                $users = $notification_type === "bulk" ? User::all() : User::whereIn('id', $recipients)->get();

                if ($notification_type === "bulk") {
                    
                    $fcms = FcmToken::whereNotNull('user_id')->get()->pluck('token')->toArray();

                    if ($send_to_guests) {
                        $guest_fcms = FcmToken::whereNull('user_id')->get()->pluck('token')->toArray();
                        $fcms = array_unique(array_merge($fcms, $guest_fcms));
                    }

                    $data = array(
                        "to" => $fcms,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => [],
                    );


                    $push_notification = new PushNotificationService();

                    $push_notification->sendPushNotification($data);
                }

                foreach ($users as $user) {
                    $user->notify($notification);
                }

                return;
            }

            // Send the notification
            return $user->notify($notification);
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "recipient_id",
                    "initiator_id",
                    "status",
                    "notification_type",
                    "post_id",
                    "comment_id",
                    "title",
                    "body",
                    "receipients",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    // Mark Notification as Read
    public function MarkPushNotificationAsRead(Request $request)
    {
        return PushNotificationService::MarkPushNotificationAsRead($request);
    }

    // // Update FCM Token
    // public function updateToken(Request $request)
    // {
    //     return PushNotificationService::updateToken($request);
    // }
}
