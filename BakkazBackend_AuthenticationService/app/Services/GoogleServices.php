<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Helpers\DateHelper;
use App\Helpers\ResponseHelpers;
use App\Models\Login;
use App\Models\Password;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleServices
{
    public static function redirectToGoogle()
    {
        return ResponseHelpers::success([
            "url" => Socialite::driver("google")
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
        ]);
    }

    public static function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver("google")->stateless()->user();
        } catch (\Exception $e) {
            return ResponseHelpers::unauthorized(
                message: "Unable to authenticate with Google."
            );
        }

        $user = User::where("email", $googleUser->email)->first();

        if (!$user) {
            $user = User::create([
                "first_name" => "",
                "last_name" => "",
                "name" => $googleUser->name,
                "email" => $googleUser->email,
                "password" => Hash::make($googleUser->password),
                "country" => "",
                "ip_address" => $request->ip(),
                "account_type" => AccountType::GoogleSignUp->value,
                "email_verified_at" => \Symfony\Component\Clock\now(),
            ]);

            Auth::login($user);

            $login = Login::create([
                "user_id" => $user->id,
                "logged_in_at" => now(),
            ]);

            MailService::sendWelcomeMail($user);

            Auth::login($user);
            $date = new DateHelper();

            $token = $user->createToken("access_token", [
                "expires_at" => $date->addMinutes(), // Expires in a day
            ]);

            $refresh_token = $user->createToken("refresh_token", [
                // "expires_at" => $date->addMiniutes(2), // Expires in 7 days
            ]);
            return ResponseHelpers::success(
                message: "Authenticated successfully.",
                statusCode: 201,
                data: [
                    "token" => $token->plainTextToken,
                    "refresh_token" => $refresh_token->plainTextToken,
                    "user" => $user,
                ]
            );
        }

        return ResponseHelpers::error(message: "Unable to signup with google");
    }
}
