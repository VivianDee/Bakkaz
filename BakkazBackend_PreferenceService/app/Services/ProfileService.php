<?php

namespace App\Services;

use App\Interfaces\ProfileInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Impl\Services\RecenthPostImpl;
use App\Models\Preference;
use App\Models\BlockedUser;
use App\Models\Privacy;
use App\Models\CustomId;
use App\Models\Profile;
use App\Models\ReportedUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\Switch_;


class ProfileService implements ProfileInterface
{
    /// Profile
    static public function showProfile(Request $request)
    {
        $param = $request->route('id');
        $user_id = $param;
        $viewer_id = $request->query('viewer_id') ? (int) $request->query('viewer_id') : null;

        if ($param) {
            $preferences = Preference::where('user_id', $param)->with('profile')->get();

            $customized_username = strtolower(ltrim($user_id, '@'));

            $custom_id = CustomId::whereRaw('LOWER(REPLACE(customized_username, "@", "")) = ?', [$customized_username])->first();

            if ($custom_id) {
                $preferences = Preference::where('id', $custom_id->preference_id)->with('profile')->get();

                $user_id = $preferences[0]->user_id;
            }
        } else {
            $preferences = Preference::with('profile')->get();
        }

        if (!$preferences) {
            return ResponseHelpers::notFound(message: "Preference not found.");
        }

        $profiles = $preferences->pluck('profile')->filter();

        $show_online_status = $preferences->pluck('privacy.show_online_status')->first();

        if ($profiles->isEmpty()) {
            return ResponseHelpers::notFound(message: "Profile not found.");
        }

         // Retrieve user information
         $user_info = AuthImpl::getUserDetails($user_id);

         if (!$user_info) {
             return ResponseHelpers::gone(message: "User Account Deleted");
         }

        $mutual_favorites_status = null;
        $blocked_status = null;


        if ($viewer_id) {
            $viewer_preference = Preference::where('user_id', $viewer_id)->first();

            if (!$viewer_preference) {
                return ResponseHelpers::notFound(message: "Viewer Preferences not found.");
            }

            $privacy = $viewer_preference->privacy;

            $blocked_status = BlockedUser::where('privacy_id', $privacy->id)
                ->where('blocked_user_id', $user_id)
                ->where('status', 'blocked')
                ->exists();

            $response = RecenthPostImpl::getMutualFavorites(user_id: $viewer_id, ref_id: $user_id);

            $mutual_favorites_status = isset($response['mutual_favourite']) ? $response['mutual_favourite'] : false;
        }

        // Add user info to each profile item
        $profiles->each(function ($profile) use ($user_info, $blocked_status, $mutual_favorites_status, $viewer_id, $show_online_status) {
            $profile->name = $user_info["name"];
            $profile->email = $user_info["email"];
            $profile->country = $user_info["country"];
            $profile->first_name = $user_info["first_name"];
            $profile->last_name = $user_info["last_name"];
            $profile->user_deleted = $user_info["user_deleted"];
            $profile->active_status = $user_info["active_status"];
            $profile->show_online_status = $show_online_status;

            if ($viewer_id) {
                $profile->blocked_status = $blocked_status;
                $profile->favorite_status = $mutual_favorites_status;
            }
        });

        return ResponseHelpers::sendResponse(data: $profiles->toArray());
    }


    static public function updateUserProfile(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'bio' => 'sometimes|string|max:255',
                "name" => 'sometimes|string|max:255',
                'country' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();

            $user_id = $request->input('user_id');

            $preference = Preference::with('profile')->where('user_id', $user_id)->first();

            if (!$preference || !$preference->profile) {
                // Return error response if preference does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: !$preference ? 'User preference not found' : 'User profile not found',
                );
            }


            $profile = $preference->profile()->where('preference_id', $preference->id)->first();

            $profile->update([
                'bio' => $data['bio']
            ]);

            $name = $data['name'] ?? '';
            $nameParts = explode(' ', $name);

            // Last name is the last part
            $last_name = array_pop($nameParts);

            // First name is everything else
            $first_name = implode(' ', $nameParts);



            $response = AuthImpl::updateUserProfile($user_id, [
                "name" => $data['name'],
                "first_name" => $first_name,
                "last_name" => $last_name,
                "country" => $data['country'],
            ]);

            if (isset($response['data']['errors'])) {
                return ResponseHelpers::error(
                    message: $response['message']
                );
            }

            return ResponseHelpers::sendResponse(
                message: 'Profile updated successfully'
            );
        } catch (ValidationException $e) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'bio'
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
