<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Models\Preference;
use App\Models\MutedUser;           
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotificationSettingsService
{
    public static function showNotificationSettings(Request $request)
    {
        $user_id = $request->route('id');

        if ($user_id) {
            $preferences = Preference::where('user_id', $user_id)->with('notification_settings')->get();
        } else {
            $preferences = Preference::with('notification_settings')->get();
        }

        if ($preferences->isEmpty()) {
            return ResponseHelpers::notFound(message: "Preference not found.");
        }

        $notification_settings = $preferences->pluck('notification_settings')->filter();

        if ($notification_settings->isEmpty()) {
            return ResponseHelpers::notFound(message: "Notification Settings not found.");
        }


        return ResponseHelpers::sendResponse(data: $notification_settings->toArray());
    }

    public static function updateNotificationSettings(Request $request)
    {
        try {
            // Validate the filtered data
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'new_favourite' => 'sometimes|boolean',
                'likes' => 'sometimes|boolean',
                'direct_messages' => 'sometimes|boolean',
                'post_comments' => 'sometimes|boolean',
                'post_replies' => 'sometimes|boolean',
                'general_notifications' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();

            $user_id = $request->input('user_id');

            $preference = Preference::with('notification_settings')->where('user_id', $user_id)->first();

            if (!$preference || !$preference->notification_settings) {
                // Return error response if preference does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: !$preference ? 'User preference not found' : 'User Notification Settings not found',
                );
            }


            $notification_settings = $preference->notification_settings()->where('preference_id', $preference->id)->first();


            $notification_settings->update($data);

            return ResponseHelpers::sendResponse(
                message: 'Notification Settings updated successfully'
            );
        } catch (ValidationException $e) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'new_favourite', 'likes', 'direct_messages',
                    'post_comments', 'post_replies', 'general_notifications'
                ])
            );
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }
}
