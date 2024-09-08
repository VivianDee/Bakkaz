<?php

namespace App\Services;

use App\Interfaces\PrivacyInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Models\Preference;
use App\Models\BlockedUser;
use App\Models\Privacy;
use App\Models\Profile;
use App\Models\ReportedUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\Switch_;


class PrivacyService implements PrivacyInterface
{
    /// User Privacy

    static public function showPrivacy(Request $request)
    {
        try {
            $user_id = $request->route('user_id');
    
            // Retrieve preferences based on user ID or all preferences if no user ID is provided
            $preferences = $user_id
                ? Preference::where('user_id', $user_id)->with('privacy')->first()
                : Preference::with('privacy')->get();



            if (!$preferences) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User preference not found'
                );
            }

            // Retrieve user information
            $user_info = AuthImpl::getUserDetails($user_id);

            if (!$user_info) {
                return ResponseHelpers::notFound(message: "User Account Not Found");
            }

            $privacy = $preferences->privacy;

            return ResponseHelpers::sendResponse(data: $privacy->toArray());
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function updatePrivacySettings(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'visibility' => 'sometimes|string|in:everyone,favourite,none',
                'privacy_mode' => 'sometimes|boolean',
                'show_online_status' => 'sometimes|boolean',
                'is_mentionable' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();


            $user_id = $request->input('user_id');

            $preference = Preference::with('privacy')
                ->where('user_id', $user_id)
                ->first();

            if (!$preference || !$preference->privacy()) {
                // Return error response if preference does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: !$preference ? 'User preference not found' : 'User privacy settings not found'
                );
            }


            $privacy = $preference->privacy()
                ->where('preference_id', $preference->id)
                ->first();

            unset($data['user_id']);

            $privacy->update($data);

            return ResponseHelpers::sendResponse(
                message: 'Privacy Settings updated successfully'
            );
        } catch (ValidationException $e) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'visibility', 'privacy_mode', 'show_online_status'
                ])
            );
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }

    static public function NonMentionables(Request $request)
    {
        try {

            $privacy = Privacy::where('is_mentionable', false)->with('preference')->get()->pluck('preference.user_id');

            return ResponseHelpers::sendResponse(data: $privacy->toArray());
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function showPrivacySettingsByIDs(Request $request)
    {
        try {
            $user_ids = $request->input('user_ids');
    
            // Retrieve preferences based on user ID or all preferences if no user ID is provided
            $privacy = Preference::whereIn('user_id', $user_ids)
            ->with('privacy')
            ->get()
            ->mapWithKeys(function ($preference) {
                return [$preference->user_id => $preference->privacy];
            });



            if ($privacy->isEmpty()) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User privacy settings not found'
                );
            }

            return ResponseHelpers::sendResponse(data: $privacy->toArray());
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }
}
