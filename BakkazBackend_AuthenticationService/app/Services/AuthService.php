<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\TokenAbility;
use App\Helpers\DateHelper;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\PreferenceImpl;
use App\Interfaces\AuthInterface;
use App\Models\Location;
use App\Models\Login;
use App\Models\Otp;
use App\Models\Password;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\OtpService;
use Carbon\Carbon;
use Cloudinary\Tag\Media;
use Illuminate\Support\Facades\Log;

class AuthService implements AuthInterface
{

    public static function getVerificationStatus(Request $request)
    {
        $user = User::where('email', request()->user()->email)->first();

        if (!$user) {
            return ResponseHelpers::notFound("User not found");
        }

        if ($user->email_verified_at) {
            return ResponseHelpers::success(
                data: [
                    "email" => $user->email,
                    "verified_at" => $user->email_verified_at,
                    "is_verified" => $user->email_verified_at ? true : false,
                ],
                message: "Email Verified",
            );
        }

        return ResponseHelpers::success(
            data: [
                "email" => $user->email,
                "verified_at" => $user->email_verified_at,
                "is_verified" => $user->email_verified_at ? true : false,
            ],
            message: "Email Not Verified",
        );
    }


    /// login
    public static function login(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                "email" => "required|email|max:255",
                "password" => "required|string",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            if (empty($request->password) && empty($request->secret_key)) {
                return ResponseHelpers::error(
                    message: "Please provide either password for login"
                );
            }

            // Check if account is deleted
            $user = User::where("email", $request->email)->first();

            if (!$user || $user->deleted) {
                return ResponseHelpers::notFound("Invalid credentials");
            }

            $credentials = $request->only("email", "password");

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // Get token expiration times
                $tokenExpTimes = TokenLifeService::getTokenExpTime();

                if ($tokenExpTimes->status() !== 200) {
                    return response()->json(
                        ["error" => "Token expiration times not set"],
                        500
                    );
                }

                $accessTokenExp = Carbon::now()->addMinutes(
                    (int) $tokenExpTimes->original["data"]["access_token_exp"]
                );
                $refreshTokenExp = Carbon::now()->addMinutes(
                    (int) $tokenExpTimes->original["data"]["refresh_token_exp"]
                );

                $accessToken = $user->createToken(
                    "access-token",
                    [TokenAbility::ACCESS_API->value],
                    $accessTokenExp
                );

                $refreshToken = $user->createToken(
                    "refresh-token",
                    [TokenAbility::ISSUE_ACCESS_TOKEN->value],
                    $refreshTokenExp
                );

                // Log the login attempt
                Login::create([
                    "user_id" => $user->id,
                    "logged_in_at" => now(),
                ]);

                $response_data = [
                    "access_token_data" => [
                        "access_token" => $accessToken->plainTextToken,
                        "expires_at" => $accessTokenExp,
                    ],
                    "refresh_token_data" => [
                        "refresh_token" => $refreshToken->plainTextToken,
                        "expires_at" => $refreshTokenExp,
                    ],
                    "user" => $user,
                ];

                // if (!$user->email_verified_at) {
                //     return ResponseHelpers::forbidden(message: "Account Not Verified");
                // }

                return ResponseHelpers::success(
                    message: "Login successful!",
                    data: $response_data
                );
            } else {
                return ResponseHelpers::unauthorized(
                    message: "Invalid credentials"
                );
            }
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "email",
                    "password",
                ])
            );
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), ["line: " . $th->getLine() . "file:" . $th->getFile()]);
            return ResponseHelpers::internalServerError(
                message: "An unexpected error occured"
            );
        }
    }

    /// register
    public static function register(Request $request)
    {
        try {
            Validator::make($request->all(), [
                "first_name" => "required|string|max:255",
                "last_name" => "required|string|max:255",
                "email" =>
                "required|email|max:255|unique:users,email|email:rfc,dns",
                // "secret_key" => "required|string|max:255", // Add required validation for secret key
                "password" => "required|string|min:8",
                "country" => "required|string|max:255",
            ]);

            foreach ($request->all() as $key => $value) {
                if (is_null($value)) {
                    $key = str_replace('_', ' ', $key);
                    return ResponseHelpers::error(ucfirst($key) . ' cannot be empty.');
                }
            }

            DB::beginTransaction();

            $existingUser = User::where(
                "email",
                $request->get("email")
            )->first();



            if ($existingUser) {
                // if (!$existingUser->email_verified_at) {
                //     DB::rollBack();
                //     return ResponseHelpers::forbidden(
                //         message: "Email already exists but is not verified. Please verify your email or use a different email address."
                //     );
                // } else {
                DB::rollBack();
                return ResponseHelpers::error(
                    message: "This email address is already in use. Please use a different email address or try recovering your account."
                );
                // }
            }

            $user = User::create([
                "name" => $request->first_name . " " . $request->last_name,
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "email" => $request->email,
                "secret_key" => $request->secret_key,
                "password" => Hash::make($request->password),
                "country" => $request->country,
                "ip_address" => $request->ip(),
                "account_type" => AccountType::AnonymousSignUp->value,
            ]);

            if ($user) {

                $res = PreferenceImpl::createPreference($user->id, 1);

                if ($res) {

                    DB::commit();

                    // self::sendOtp($request);
                    MailService::sendWelcomeMail($user);

                    return ResponseHelpers::created(
                        message: "User Created Successfully"
                    );
                } else {
                    MailService::sendWelcomeMail($user);

                    return ResponseHelpers::error(
                        message: "User Created Successfully. Preferences Not Set"
                    );
                }
            }

            DB::rollBack();
            return ResponseHelpers::error(
                message: "Unable to complete Registration. Please try again"
            );
        } catch (ValidationException $e) {
            DB::rollBack();

            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "first_name",
                    "last_name",
                    "email",
                    "password",
                    "country",
                ])
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    public static function verifyAccount(Request $request)
    {
        $email = $request->input("email");
        $otp = $request->input("otp");

        // check if account is deleted
        $user = User::where("email", $email)->get()->first();

        if ($user->email_verified_at) {
            // OTP is valid
            // Optionally, delete the OTP record to prevent reuse
            Otp::where("email", $email)->where("otp", $otp)->delete();

            return ResponseHelpers::success(
                message: "Account Already Verified"
            );
        }

        // Fetch the OTP record from the database
        $otpRecord = Otp::where("email", $email)
            ->where("otp", $otp)
            ->where("expires_at", ">", now())
            ->first();

        if ($otpRecord) {
            // OTP is valid
            // Optionally, delete the OTP record to prevent reuse
            Otp::where("email", $email)->where("otp", $otp)->delete();

            $user = User::where("email", $email)->get()->first();

            $user->update([
                "email_verified_at" => now(),
            ]);

            // MailService::sendWelcomeMail($user);

            return ResponseHelpers::success(
                message: "Account verified successfully"
            );
        } else {
            // OTP is invalid or expired
            return ResponseHelpers::notFound(message: "Invalid or expired OTP");
        }
    }

    /// recover account
    public static function sendOtp(Request $request)
    {
        $email = $request->route("email") ?? $request->email;

        // Find USER
        $user = User::where("email", $email)->get()->first();

        if (!$user) {
            return ResponseHelpers::notFound(message: "User not found");
        }

        try {
            // Generate OTP
            $res = OtpService::generateOtp(6, $email);

            if ($res["otp"] == null) {
                return ResponseHelpers::notFound(message: $res["message"]);
            }

            // Send OTP email
            $sent = MailService::sendOtpMail($user, $res["otp"]);

            if ($sent) {
                return ResponseHelpers::success(message: $res["message"]);
            }
        } catch (\Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function verifyOtp(Request $request)
    {
        $email = $request->input("email");
        $otp = $request->input("otp");

        // Fetch the OTP record from the database
        $otpRecord = Otp::where("email", $email)
            ->where("otp", $otp)
            ->where("expires_at", ">", now())
            ->first();

        if ($otpRecord) {
            // Optionally, delete the OTP record to prevent reuse
            Otp::where("email", $email)->where("otp", $otp)->delete();

            return ResponseHelpers::success(
                message: "Otp veified successfully"
            );
        }
        return ResponseHelpers::unprocessableEntity(
            message: "Invalid Otp - Unable to validate OTP"
        );
    }

    public static function changePassword(Request $request)
    {
        $email = $request->input("email");
        $newPassword = $request->input("new_password");
        // Find USER
        $user = User::where("email", $email)->get()->first();

        if (!$user) {
            return ResponseHelpers::notFound(message: "User not found");
        }

        // Update user's password
        $user->update([
            "password" => Hash::make($newPassword),
        ]);

        $password = Password::create([
            "user_id" => $user->id,
            "password" => Hash::make($request->new_password),
            "changed_at" => now(),
        ]);

        return ResponseHelpers::success(
            message: "Password changed successfully"
        );
    }

    /// logout
    public static function logout(Request $request)
    {
        $user_id = $request->user_id;

        if (Auth::check() && Auth::id() === $user_id) {
            $user = User::find($user_id);
            Auth::logout();
            $user->tokens()->delete();
            return ResponseHelpers::success(message: "Logged Out");
        }
    }

    /// auth_info
    public static function saveDevice(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "user_id" => "required|integer|exists:users,id",
                "device_name" => "required|string",
                "device_model" => "required|string",
                "device_imei" => "required|string",
                "device_software_version" => "required|string",
            ]);
            $user_id = $request->user_id;

            if (!User::where("id", $user_id)->exists()) {
                return ResponseHelpers::notFound();
            }

            $deviceName = $request->input("device_name");
            $deviceModel = $request->input("device_model");
            $deviceImei = $request->input("device_imei");
            $deviceSoftwareVersion = $request->input("device_software_version");

            $user_device = UserDevice::create([
                "user_id" => $user_id,
                "device_name" => $deviceName,
                "device_model" => $deviceModel,
                "device_imei" => $deviceImei,
                "device_software_version" => $deviceSoftwareVersion,
            ]);

            if ($user_device) {
                return ResponseHelpers::success(
                    message: "User Device registered!"
                );
            }
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "first_name",
                    "last_name",
                    "email",
                    "secret_key",
                    "password",
                    "country",
                    "ip_address",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }
    public static function savePresentLocation(Request $request)
    {
        try {
            Validator::make($request->all(), [
                "user_id" => "required|integer|exists:users,id",
                "ip_address" => "required",
                "latitude" => "required|numeric",
                "longitude" => "required|numeric",
                "city" => "required|string",
                "state" => "required|string",
                "country" => "required|string",
                "postal_code" => "nullable|string",
            ]);
            $user_id = $request->user_id;

            if (!User::where("id", $user_id)->exists()) {
                return ResponseHelpers::notFound();
            }

            $user_location = Location::create([
                "user_id" => $user_id,
                "ip_address" => $request->ip(),
                "latitude" => $request->get("latitude"),
                "longitude" => $request->get("longitude"),
                "city" => $request->get("city"),
                "region" => $request->get("state"),
                "country" => $request->get("country"),
                "postal_code" => $request->get("postal_code"),
            ]);

            if ($user_location) {
                return ResponseHelpers::success(message: "Locatiion updated");
            }
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "first_name",
                    "last_name",
                    "email",
                    "secret_key",
                    "password",
                    "country",
                    "ip_address",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }
    public static function savePasswordHistory(Request $request)
    {
        try {
            Validator::make($request->all(), [
                "user_id" => "required|integer|exists:users,id",
                "password" => "required|string",
                "changed_at" => "required|datetime",
            ]);
            $user_id = $request->user_id;

            if (!User::where("id", $user_id)->exists()) {
                return ResponseHelpers::notFound();
            }

            $user_location = Password::create([
                "user_id" => $user_id,
                "password" => $request->get("password"),
                "changed_at" => $request->get("changed_at"),
            ]);

            if ($user_location) {
                return ResponseHelpers::success(
                    message: "Password History updated"
                );
            }

            return ResponseHelpers::error(
                message: "User login failed to update successfully"
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "first_name",
                    "last_name",
                    "email",
                    "secret_key",
                    "password",
                    "country",
                    "ip_address",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }
    public static function saveLoginHistory(Request $request)
    {
        try {
            // Validation
            Validator::make($request->all(), [
                "user_id" => "required|integer|exists:users,id",
            ]);

            $user_id = $request->user_id;

            if (!User::where("id", $user_id)->exists()) {
                return ResponseHelpers::notFound();
            }
            $user_login = Login::create([
                "user_id" => $user_id,
                "logged_in_at" => now(),
            ]);

            if ($user_login) {
                return ResponseHelpers::success(message: "User login updated");
            }

            return ResponseHelpers::error(
                message: "User login failed to update successfully"
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "first_name",
                    "last_name",
                    "email",
                    "secret_key",
                    "password",
                    "country",
                    "ip_address",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }
    /// session
    public static function refresh(Request $request)
    {
        $user = $request->user();
        // Get token expiration times
        $tokenExpTimes = TokenLifeService::getTokenExpTime();

        if ($tokenExpTimes->status() !== 200) {
            return response()->json(
                ["error" => "Token expiration times not set"],
                500
            );
        }

        $accessTokenExp = Carbon::now()->addMinutes(
            (int) $tokenExpTimes->original["data"]["access_token_exp"]
        );

        $accessToken = $user->createToken(
            "access-token",
            [TokenAbility::ACCESS_API->value],
            $accessTokenExp
        )->plainTextToken;

        $data = [
            "access_token_data" => [
                "access_token" => $accessToken,
                "expires_at" => $accessTokenExp,
            ],
        ];

        return ResponseHelpers::success(
            message: "Access token refreshed successfully!",
            data: $data
        );
    }
}
