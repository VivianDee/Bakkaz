<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Models\Preference;
use App\Models\MutedUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MutedUsersService
{

    static public function showMutedUsers(Request $request)
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

            $muted_users = $privacy->mutedUsers()->get();

            return ResponseHelpers::sendResponse(data: $muted_users->toArray());
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function muteUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'muted_user_id' => 'required|integer'
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

            
            $muted_user = mutedUser::where('privacy_id', $privacy->id)
                ->where('muted_user_id', $request->input('muted_user_id'))
                ->first();

            if ($muted_user) {
                $muted_user->update([
                    'status' => $muted_user->status === "muted" ? "unmuted" : "muted"
                ]);
            } else {
                $muted_user = $privacy->mutedUsers()->create([
                    'privacy_id' => $privacy->id,
                    'muted_user_id' => $request->input('muted_user_id'),
                    'status' => $request->input('status', 'muted'),
                ]);
            }

           

            return ResponseHelpers::sendResponse(
                message: "User {$muted_user->status} successfully"
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
