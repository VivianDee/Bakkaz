<?php

namespace App\Services;

use App\Interfaces\BlockUsersInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Models\Preference;
use App\Models\BlockedUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class BlockUsersService
{
    /// Blocked Users

    static public function showBlockedUsers(Request $request)
    {
        try {

            $user_id = $request->route('id');


            $preference = Preference::with('privacy')->where('user_id', $user_id)->first();

            if (!$preference || !$preference->privacy) {
                // Return error response if preference or privacy settings does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: !$preference ? 'User preference not found' : 'User privacy settings not found',
                );
            }

            $privacy = $preference->privacy;

            $blocked_users = $privacy->blockedUsers()->where('status', 'blocked')->get();

            if ($blocked_users->isEmpty()) {
                return ResponseHelpers::success(
                    message: "No Blocked Users Found.",
                    data: []
                );
            }

            // Add user info to each profile item
            $blocked_users->each(function ($blocked_user) {

                $user_info = AuthImpl::getUserDetails($blocked_user->blocked_user_id);


                // Get the user's preference
                $preference = Preference::where('user_id', $blocked_user->blocked_user_id)->first();

                if ($preference) {
                    // Get custom ID
                    $custom_id = $preference->custom_id()
                        ->where('preference_id',  $preference->id)
                        ->where('status', "Active")
                        ->first();

                    $blocked_user->name = isset($user_info["name"]) ? $user_info["name"] : null;
                } else {
                    $blocked_user->name = null;
                }
                $blocked_user->custom_id = isset($custom_id->customized_username) ? $custom_id->customized_username : null;
            });

            return ResponseHelpers::sendResponse(data: $blocked_users->toArray());
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }




    /// Block and unblock Users

    static public function blockUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'blocked_user_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user_id = $request->input('user_id');

            $preference = Preference::with('privacy')->where('user_id', $user_id)->first();

            if (!$preference || !$preference->privacy) {
                // Return error response if preference or privacy settings does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: !$preference ? 'User preference not found' : 'User privacy settings not found',
                );
            }

            $privacy = $preference->privacy;

            $blocked_user = BlockedUser::where('privacy_id', $privacy->id)
                ->where('blocked_user_id', $request->input('blocked_user_id'))
                ->first();

            if ($blocked_user) {

                $blocked_user->update([
                    'status' => $blocked_user->status === "blocked" ? "unblocked" : "blocked"
                ]);
            } else {

                $blocked_user = $privacy->blockedUsers()->create([
                    'privacy_id' => $privacy->id,
                    'blocked_user_id' => $request->input('blocked_user_id'),
                    'status' => $request->input('status', 'blocked'),
                ]);
            }

            return ResponseHelpers::sendResponse(
                message: "User {$blocked_user->status} successfully"
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
