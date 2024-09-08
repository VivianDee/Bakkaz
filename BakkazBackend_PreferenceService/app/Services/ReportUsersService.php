<?php

namespace App\Services;

use App\Interfaces\ReportUsersInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Models\Preference;
use App\Models\ReportedUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ReportUsersService
{


    /// Report Users

    static public function ReportUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'reported_user_id' => 'required|integer',
                'resolved' => 'sometimes|boolean',
                'reviewed' => 'sometimes|boolean',
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

            // Check if the user 1 has already reported user 2
            $existingReport = ReportedUser::where('privacy_id', $privacy->id)
                ->where('reported_user_id', $request->reported_user_id)
                ->first();

            if ($existingReport) {
                // Return response if the user has already been reported
                return ResponseHelpers::success(
                    message: 'User reported successfully'
                );
            }

            $reported_user = ReportedUser::create([
                'privacy_id' => $privacy->id,
                'reported_user_id' => $request->input('reported_user_id'),
                'resolved' => $request->input('resolved') ?? false,
                'reviewed' => $request->input('reviewed') ?? false,
            ]);

            return ResponseHelpers::sendResponse(
                message: 'User reported successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'reported_user_id', 'resolved', 'reviewed'
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

    static public function showReportedUsers(Request $request)
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


            $reported_users = $privacy->reportedUsers()->get();

            if ($reported_users->isEmpty()) {
                return ResponseHelpers::success(
                    message: "No Reported Users Found.",
                    data: []
                );
            }

            // Add user info to each profile item
            $reported_users->each(function ($reported_user) {

                $user_info = AuthImpl::getUserDetails($reported_user->reported_user_id);

                if (empty($user_info)) {
                    return;
                }

                // Get the user's preference
                $preference = Preference::where('user_id', $reported_user->reported_user_id)->first();

                if ($preference) {
                    // Get custom ID
                    $custom_id = $preference->custom_id()
                        ->where('preference_id',  $preference->id)
                        ->where('status', "Active")
                        ->first();

                    $reported_user->name = isset($user_info["name"]) ? $user_info["name"] : null;
                } else {
                    $reported_user->name = null;
                }
                $reported_user->custom_id = isset($custom_id->customized_username) ? $custom_id->customized_username : null;
            });

            return ResponseHelpers::sendResponse(data: $reported_users->toArray());
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }
}
