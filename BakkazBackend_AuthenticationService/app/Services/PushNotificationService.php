<?php

namespace App\Services;

use App\Enums\BakkazServiceType;
use App\Helpers\ResponseHelpers;
use App\Models\Notification as ModelsNotification;
use Kreait\Firebase\Messaging\ApnsConfig;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use VARIANT;

class PushNotificationService
{
    protected static $firebaseCredentials;

    public function __construct()
    {
        self::$firebaseCredentials = env("FIREBASE_CREDENTIALS_JSON");
    }

    // Notifications
    public static function showNotifications(Request $request)
    {
        try {
            $user_id = $request->route("user_id");
            $service = $request->header("service")
                ? BakkazServiceType::from($request->header("service"))->value
                : "";

            if (!$service) {
                return ResponseHelpers::error(message: "Service Not Found");
            }

            $query = ModelsNotification::query();

            if ($user_id) {
                $query->where("notifiable_id", $user_id);
            }


            $notifications = $query->where('type', "!=", "App\Notifications\MessageNotification")
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service')) = ?", [$service])
                ->get();

            if (in_array($service, ['recenth-posts-service'])) {
                
                $query = ModelsNotification::query();

                if ($user_id) {
                    $query->where("notifiable_id", $user_id);
                }

                // Define the fallback query with the alternative service
                $fallback_query = $query->where('type', "!=", "App\Notifications\MessageNotification")
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service')) = ?", ["recenth-post-service"]);
                // Merge the results of the original and fallback queries
                $notifications = $notifications->merge($fallback_query->get());
            }


            if ($notifications->isEmpty()) {
                return ResponseHelpers::success(
                    data: [],
                    message: "No Notifications Found"
                );
            }

            // Sort the merged collection by 'created_at' in descending order
            $notifications = $notifications->sortByDesc('created_at')->values();

            // Decode JSON data in notifications
            $notifications = $notifications->map(function ($notification) {
                $notification->data = json_decode($notification->data);

                if (isset($notification->data->initiator->mutual_favourite)) {
                    $notification->data->initiator->mutual_favourite = (bool) $notification->data->initiator->mutual_favourite;
                }



                // Remove the namespace from the type and notifiable_type
                $notification->type = class_basename($notification->type);
                $notification->notifiable_type = class_basename(
                    $notification->notifiable_type
                );
                unset($notification->data->service);

                return $notification;
            });

            return ResponseHelpers::success(data: $notifications);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    public static function updateMutualFavourites(Request $request)
    {
        DB::beginTransaction();
        try {

            // Validation
            $validator = Validator::make($request->all(), [
                "user_id" => "required|integer",
                "ref_id" => "required|integer",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            $user_id = $request->input("user_id");
            $ref_id = $request->input("ref_id");

            $service = $request->header("service")
                ? BakkazServiceType::from($request->header("service"))->value
                : "";

            if (!$service) {
                return ResponseHelpers::error(message: "Service not included in header");
            }


            $notifications = ModelsNotification::whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.service')) = ?",
                [$service]
            )->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.initiator.id')) = ?",
                [$ref_id]
            )->where('notifiable_id', $user_id)->get();

            if ($notifications->isEmpty()) {
                return ResponseHelpers::success(
                    data: [],
                    message: "No Notifications Found"
                );
            }

            foreach ($notifications as $notification) {
                $data = json_decode($notification->data, true);
                if (isset($data['initiator']['mutual_favourite'])) {
                    $status = $data['initiator']['mutual_favourite'];

                    $data['initiator']['mutual_favourite'] = !$status;

                    // Save the updated data back to the notification
                    $notification->data = json_encode($data);
                    $notification->save();
                }
            }

            DB::commit();
            return ResponseHelpers::success(
                message: "Mutual Favourites Updated Successfully"
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "user_id",
                    "ref_id",
                    "status"
                ])
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    public static function sendPushNotification(array $data)
    {
        try {
            $deviceTokens = is_array($data["to"]) ? $data["to"] : [$data["to"]];

            // Retrieve and decode the Firebase credentials from the environment variable
            $firebaseCredentialsJson = self::$firebaseCredentials;
            if (!$firebaseCredentialsJson) {
                throw new \Exception("Firebase credentials not found in the environment variables.");
            }

            $firebaseCredentials = json_decode($firebaseCredentialsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON in Firebase credentials: " . json_last_error_msg());
            }

            // Initialize Firebase with the service account credentials
            $firebase = (new Factory())->withServiceAccount(
                $firebaseCredentials
            );
            $messaging = $firebase->createMessaging();

            $response = $messaging->validateRegistrationTokens($deviceTokens);

            $valid_tokens = $response["valid"];
            // $valid_tokens = $deviceTokens;

            if (empty($valid_tokens)) {
                throw new \Exception("No valid FCM Tokens Found");
            }

            // Create the push notification message
            $message = CloudMessage::new()
                ->withNotification(
                    Notification::create(
                        $data["notification"]["title"],
                        $data["notification"]["body"]
                    )
                )
                ->withData($data["data"]);

            // Send the push notification to the device tokens
            $messaging->sendMulticast($message, $valid_tokens);

            if (empty($response["unknown"]) && empty($response["invalid"])) {
                return ResponseHelpers::success(
                    message: "Push notification sent successfully"
                );
            }

            return ResponseHelpers::success(
                message: "Push notifications sent successfully"
            );
        } catch (InvalidMessage $e) {
            // Log the error message, file, and line number
            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return ResponseHelpers::error(
                message: $e->getMessage()
            );
        } catch (\Throwable $th) {
            // Log the error message, file, and line number
            Log::error($th->getMessage(), ['file' => $th->getFile(), 'line' => $th->getLine()]);
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    // Mark Notification as Read
    public static function MarkPushNotificationAsRead(Request $request)
    {
        try {
            $notificationId = $request->input("notification_id");
            $userId = $request->input("user_id");

            if ($notificationId) {
                // Fetch a single notification by ID
                $notification = ModelsNotification::where('id', $notificationId)->first();

                if (!$notification) {
                    return ResponseHelpers::notFound(
                        message: "Notification Not Found"
                    );
                }

                // Update the single notification
                $notification->update(['read_at' => now()]);
            } else {
                // Fetch all unread notifications for the user
                $notifications = ModelsNotification::where('notifiable_id', $userId)
                    ->whereNull('read_at')
                    ->get();

                if ($notifications->isEmpty()) {
                    return ResponseHelpers::success(
                        message: "No Unread Notifications Found"
                    );
                }

                // Update all notifications in a single query
                ModelsNotification::whereIn('id', $notifications->pluck('id'))
                    ->update(['read_at' => now()]);
            }

            return ResponseHelpers::success(
                message: "Notification(s) Marked As Read"
            );
        } catch (\Throwable $th) {
            // Log the error message, file, and line number
            Log::error($th->getMessage(), ['file' => $th->getFile(), 'line' => $th->getLine()]);
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }


    // Update FCM Token
    // public static function updateToken(Request $request)
    // {
    //     try {
    //         // Validation
    //         $validator = Validator::make($request->all(), [
    //             "user_id" => "required|exists:users,id",
    //             "fcm" => "required|string|unique:users,fcm",
    //         ]);

    //         throw_if($validator->fails(), new ValidationException($validator));

    //         $user = User::where("id", $request->input("user_id"))->first();

    //         if (empty($user)) {
    //             return ResponseHelpers::error(
    //                 message: "Unable to update FCM token"
    //             );
    //         }

    //         $user->fcm = $request->input("fcm");

    //         $user->save();

    //         return ResponseHelpers::success(
    //             message: "FCM token updated successfully"
    //         );
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return ResponseHelpers::internalServerError(
    //             message: $th->getMessage()
    //         );
    //     }
    // }
}
