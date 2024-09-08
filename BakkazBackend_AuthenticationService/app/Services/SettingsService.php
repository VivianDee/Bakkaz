<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use App\Interfaces\SettingsInterface;
use App\Models\Password;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingsService
{
    public static function changePassword(Request $request)
    {
        try {

            // Validation
            $validator = Validator::make($request->all(), [
                "email" => "required|email|max:255",
                "new_password" => "required|string",
                "old_password" => "required|string",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            $email = $request->input("email");
            $newPassword = $request->input("new_password");
            $oldPassword = $request->input("old_password");

            if ($request->new_password === $request->old_password) {
                return ResponseHelpers::error(message: "The new password cannot be the same as the old password.");
            }

            // Find user
            $user = User::where("email", $email)->get()->first();

            if (!$user) {
                return ResponseHelpers::notFound(message: "User not found");
            }

            // Check if the old password matches
            if (!Hash::check($oldPassword, $user->password)) {
                return ResponseHelpers::error(message: "The provided old password does not match our records.");
            }

            // Update user's password
            $user->update([
                "password" => Hash::make($newPassword),
            ]);

            $password = Password::create([
                "user_id" => $user->id,
                "password" => Hash::make($newPassword),
                "changed_at" => now(),
            ]);

            return ResponseHelpers::success(
                message: "Password changed successfully"
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "email",
                    "old_password",
                    "new_password"
                ])
            );
        }  catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
}
