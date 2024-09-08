<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use Illuminate\Http\Request;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FCMTokenService
{
    // Srore FCM Token

    static public function storeFcmToken(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                "user_id" => "nullable|exists:users,id",
                "fcm" => "required|string",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            $user = User::where("id", $request->input("user_id"))->first();

            // Check if the Guest token already exists for the guest
            $existingGuestToken = FcmToken::whereNull('user_id')
                ->where('token', $request->fcm)
                ->first();

            if ($existingGuestToken) {
                if ($user) {
                    // Save the new token
                    $existingGuestToken->update([
                        'user_id' => $user->id ?? null,
                        'token' => $request->fcm,
                    ]);
                }

                return ResponseHelpers::success(
                    message: "FCM token updated successfully"
                );
            }

            if ($user) {
                // Check if the token already exists for the user
                $existingToken = FcmToken::where('user_id', $user->id)
                    ->where('token', $request->fcm)
                    ->first();
            }

            if (empty($existingToken)) {
                // Save the new token
                FcmToken::create([
                    'user_id' => $user->id ?? null,
                    'token' => $request->fcm,
                ]);
            }

            return ResponseHelpers::success(
                message: "FCM token updated successfully"
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    static public function removeFcmToken(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                "user_id" => "required|exists:users,id",
                "fcm" => "required|string",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            $user = User::where("id", $request->input("user_id"))->first();

            if (empty($user)) {
                return ResponseHelpers::error(
                    message: "Unable to delete FCM token"
                );
            }

            // Remove the specified token
            FcmToken::where('user_id', $user->id)
                ->where('token', $request->fcm)
                ->delete();

            return ResponseHelpers::success(
                message: "FCM token deleted successfully"
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
}
