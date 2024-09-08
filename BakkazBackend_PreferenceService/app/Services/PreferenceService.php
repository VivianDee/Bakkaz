<?php

namespace App\Services;

use App\Enums\BakkazServiceType;
use App\Interfaces\PreferenceInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Helpers\GenerateCustomId;
use App\Models\Preference;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Impl\Services\AuthImpl;
use App\Impl\Services\PaymentImpl;
use App\Impl\CurrencyConverter;
use App\Models\CustomId;
use App\Models\PaymentAmount;
use App\Models\BlockedUser;
use App\Models\Verifications;
use App\Impl\Services\RecenthPostImpl;
use Carbon\Carbon;


class PreferenceService implements PreferenceInterface
{
    ///User Prefrences

    static public function showPreference(Request $request)
    {
        try {
            $param = $request->route('id');
            $user_id = $param;
            $viewer_id = $request->query('viewer_id') ? (int) $request->query('viewer_id') : null;

            // Retrieve preferences based on user ID or all preferences if no user ID is provided
            if ($param) {
                $preferences = Preference::where('user_id', $param)->with('profile', 'privacy', 'security', 'notification_settings')->get();

                $customized_username = strtolower(ltrim($user_id, '@'));

                $custom_id = CustomId::whereRaw('LOWER(REPLACE(customized_username, "@", "")) = ?', [$customized_username])->first();

                if ($custom_id) {
                    $preferences = Preference::where('id', $custom_id->preference_id)->with('profile', 'privacy', 'security', 'notification_settings')->get();

                    $user_id = $preferences[0]->user_id;
                }
            } else {
                $preferences = Preference::with('profile', 'privacy', 'security', 'notification_settings')->get();
            }



            if ($preferences->isEmpty()) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User preference not found'
                );
            }

            // Retrieve user information
            $user_info = AuthImpl::getUserDetails($user_id);

            if (!$user_info) {
                return ResponseHelpers::gone(message: "User Account Deleted");
            }

            $show_online_status = $preferences->pluck('privacy.show_online_status')->first();

            // Add user custom_id to each preference item
            $preferences->each(function ($preference) {
                $custom_id = CustomId::where('preference_id', $preference->id)->where('status', "active")->first();

                $preference->custom_id = isset($custom_id->customized_username) ? $custom_id->customized_username : null;
            });

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

            // Add user info to each preference item
            $preferences->each(function ($preference) use ($user_info, $blocked_status, $mutual_favorites_status, $viewer_id, $show_online_status) {
                $preference->profile->name = $user_info["name"] ?? null;
                $preference->profile->email = $user_info["email"] ?? null;
                $preference->profile->country = $user_info["country"] ?? null;
                $preference->profile->first_name = $user_info["first_name"] ?? null;
                $preference->profile->last_name = $user_info["last_name"] ?? null;
                $preference->profile->user_deleted = $user_info["user_deleted"] ?? null;
                $preference->profile->active_status = $user_info["active_status"] ?? null;
                $preference->profile->show_online_status = $show_online_status ?? null;

                if ($viewer_id) {
                    $preference->profile->blocked_status = $blocked_status ?? null;
                    $preference->profile->favorite_status = $mutual_favorites_status ?? null;
                }
            });

            return ResponseHelpers::sendResponse(data: $preferences->toArray());
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function createPreference(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'bio' => 'sometimes|string|max:255',
                'visibility' => 'sometimes|string|in:everyone,favourite,none',
                'privacy_mode' => 'sometimes|boolean',
                'is_verified' => 'sometimes|boolean',
                'suscribed' => 'sometimes|boolean',
                'subscription_id' => 'sometimes|integer',
                'language' => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $service = BakkazServiceType::from($request->header('service'));
            $user_id = $request->input('user_id');


            // Check if preference already exists for the user
            $preference = Preference::where('user_id', $user_id)->first();

            if ($preference) {
                return ResponseHelpers::sendResponse(message: 'User Prefrence Already Exists');
            }


            // Create new preference
            $preference = Preference::create([
                'user_id' => $user_id,  // Retrieve the user id from the request
                'is_verified' => $request->input('is_verified', false),
                'language' => $request->input('language'),
                'suscribed' => $request->input('suscribed', false),
                'subscription_id' => $request->input('suscribed', false),
                'service' => $service
            ]);





            // Create profile and privacy settings for the preference
            $profile = $preference->profile()->create([
                'preference_id' => $preference->id,
                'bio' => $request->input('bio'),
            ]);

            $privacy = $preference->privacy()->create([
                'preference_id' => $preference->id,
                'visibility' => $request->input('visibility', 'none'),
                'privacy_mode' => $request->input('privacy_mode', false),
            ]);

            $security = $preference->security()->create([
                'preference_id' => $preference->id,
                'remeber_me' => true,
                'biometric_id' => false,
                'face_id' => false,
                'sms_authenticator' => false,
                'google_authenticator' => false,
            ]);

            $custom_id = $preference->custom_id()->create([
                "customized_username" => GenerateCustomId::generateId(),
                "status" => "active",
                "payment_status" =>  "FREE_ID",
                "payment_ref" =>  "FREE_ID",
                "payment_initialized_at" => Carbon::now()
            ]);

            $notification_settings =  $preference->notification_settings()->create([
                'new_favourite' => true,
                'likes' => true,
                'direct_messages' => true,
                'post_comments' => true,
                'post_replies' => true,
                'general_notifications' => true
            ]);

            return ResponseHelpers::sendResponse(message: 'User Prefrence Created Successfully');
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'bio',
                    'visibility',
                    'privacy_mode',
                    'is_verified',
                    'language',
                    'suscribed',
                    'subscription_id'
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


    static public function updatePreference(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'bio' => 'sometimes|string|max:255',
                'visibility' => 'sometimes|string|in:everyone,favourite,none',
                'privacy_mode' => 'sometimes|boolean',
                'subscribed' => 'sometimes|boolean',
                'subscription_id' => 'sometimes|integer',
                'is_verified' => 'sometimes|boolean',
                'language' => 'sometimes|integer',

            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();


            // Retrieve preference and related models
            $preference = Preference::with('profile', 'privacy')->where('user_id', $request->input('user_id'))->first();

            if (!$preference) {
                return ResponseHelpers::success(
                    message: 'User Preferences Settings not found',
                    data: []
                );
            }

            // Update preference, profile, and privacy settings
            $preference->update($data);
            $preference->profile->update($data);
            $preference->privacy->update($data);


            return ResponseHelpers::sendResponse(message: 'User Prefrence Updated Successfully');
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'bio',
                    'visibility',
                    'privacy_mode',
                    'is_verified',
                    'language',
                    'suscribed',
                    'subscription_id'
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


    static public function deletePreference(Request $request)
    {
        try {
            $user_id = $request->route('user_id');


            // Retrieve preference by ID
            $preference = Preference::where('user_id', $user_id)->first();

            if (!$preference) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User Preferences Settings not found'
                );
            }

            $preference->delete();

            return ResponseHelpers::sendResponse(message: 'User Prefrence Deleted Successfully');
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }

    static public function restorePreference(Request $request)
    {
        try {
            $user_id = $request->route('user_id');


            // Retrieve preference by ID
            $preference = Preference::withTrashed()->where('user_id', $user_id)->first();

            if (!$preference) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User Preferences Settings not found'
                );
            }

            // Restore the soft-deleted preference
            $preference->restore();

            return ResponseHelpers::sendResponse(message: 'User Prefrence Restored Successfully');
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }






    /// Subscription


    static public function Issuscribed(Request $request)
    {
        try {
            $user_id = $request->route('id');


            // Retrieve preference by user ID
            $preference = Preference::where('user_id', $user_id)->first();

            if (!$preference) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User Preferences Settings not found'
                );
            }

            return ResponseHelpers::sendResponse(data: [
                'subscribed' => $preference->subscribed,
                'subscription_id' => $preference->subscription_id
            ]);
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }



    static public function updateSubscription(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'subscribed' => 'required|boolean',
                'subscription_id' => 'required|integer',
            ]);


            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();


            // Retrieve preference by user ID
            $preference = Preference::where('user_id', $request->input('user_id'))->first();

            if (!$preference) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User Preferences Settings not found'
                );
            }

            // Update subscription preferences
            $preference->update($data);

            return ResponseHelpers::sendResponse(
                message: 'Subcription Preferences Updated Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'subscribed',
                    'subscription_id'
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




    /// Language

    static public function updateLanguage(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'language' => 'required|integer',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();


            // Retrieve preference by user ID
            $preference = Preference::where('user_id', $request->input('user_id'))->first();

            if (!$preference) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User Preferences Settings not found'
                );
            }


            // Update language preferences
            $preference->update($data);

            return ResponseHelpers::sendResponse(
                message: 'Language Preference Updated Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'language'
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





    /// Custom ID

    static public function initializeCustomIdPayment(Request $request)
    {
        try {

            $amount = PaymentAmount::where("type", "custom_id")->first()->amount;
            $user_id = $request->input('user_id');

            $request->merge(['amount' => strval($amount)]);

            $user_info = AuthImpl::getUserDetails($user_id);

            if (isset($user_info['country']) && $user_info['country'] === "NG") {
                $request->merge(['currency' => "NGN"]);
                $exchanger = new CurrencyConverter();


                $amount = $exchanger->convert($amount, "USD",  "NGN");

                $request->merge(['amount' => strval(round($amount, 2))]);
            } else if (isset($user_info['country']) && $user_info['country'] !== "NG") {
                $request->merge(['currency' => "USD"]);
            }


            // Retrieve preference by user ID
            $preference = Preference::where('user_id', $user_id)->first();

            if (!$preference) {
                return ResponseHelpers::notFound(
                    message: 'User Preferences Settings not found'
                );
            }


            // Check if the last custom ID change was within the last 3 months
            $last_custom_id = $preference->custom_id()->where('preference_id', $preference->id)
                ->where('status', "active")
                ->where('payment_status', '!=', "FREE_ID")
                ->first();

            if ($last_custom_id && Carbon::parse($last_custom_id->created_at)->gt(Carbon::now()->subMonths(3))) {
                return ResponseHelpers::error(
                    message: 'You can only customize your ID once every 3 months.'
                );
            }

            // Check for any existing custom ID with payment status 'pending'
            $custom_id = $preference->custom_id()->where('preference_id', $preference->id)
                ->where('payment_status', 'pending')
                ->first();

            if ($custom_id) {
                $checkResponse = self::verifyCustomIdPayment($request, $custom_id->payment_ref);

                $check = $checkResponse->getData(true);


                if ($check['status'] && isset($check['data']['payment_ref'])) {
                    $custom_id->update([
                        "payment_status" => "processed",
                        "payment_verified_at" => Carbon::now(),
                    ]);


                    return ResponseHelpers::success(
                        message: "Payment For Custom ID Found and Verified",
                        data: [
                            "payment_ref" => $custom_id->payment_ref
                        ]
                    );
                }
            }


            // Initialize payment process
            $response = PaymentImpl::initializePayment($request);

            if (!isset($response['payment_reference'])) {
                return ResponseHelpers::error(
                    message: "Error intializing payment: {$response}"
                );
            }

            if (empty($custom_id)) {

                // If no pending custom ID, create a new one with payment status 'pending'
                $preference->custom_id()->create([
                    "customized_username" => null,
                    "status" => "pending",
                    "payment_status" => "pending",
                    "payment_ref" => $response['payment_reference'],
                    "payment_initialized_at" => Carbon::now()
                ]);

                return ResponseHelpers::success(
                    message: 'Payment For Custom ID Has Been Successfully Initiated.',
                    data: $response
                );
            }

            // If a pending custom ID exists, update the payment reference
            $custom_id->update([
                "payment_ref" => $response['payment_reference'],
            ]);

            return ResponseHelpers::success(
                message: 'Payment For Custom ID Has Been Successfully Initiated.',
                data: $response
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function verifyCustomIdPayment(Request $request, $payment_ref = null)
    {
        try {

            $payment_reference = $payment_ref ? $payment_ref : $request->query('payment_ref');

            // Check if payment reference is provided
            if (!$payment_reference) {
                return ResponseHelpers::error(message: 'No Payment Reference Inputed');
            }

            // Retrieve custom ID based on payment reference
            $custom_id = CustomId::where('payment_ref', $payment_reference)->first();

            if (!$custom_id) {
                return ResponseHelpers::notFound(message: 'Payment For Custom ID Not Found');
            }


            // Check if payment status is already processed
            if ($custom_id->payment_status === "processed") {
                return ResponseHelpers::success(
                    message: "Payment For Custom ID Has Been Successfully Verified",
                    data: [
                        "payment_ref" => $custom_id->payment_ref
                    ]
                );
            }

            //Verify payment
            $response = PaymentImpl::verifyPayment($payment_reference);

            // If payment verification is successful, update payment status
            if ($response['status'] === "success") {
                $custom_id->update([
                    "payment_status" => "processed",
                    "payment_verified_at" => Carbon::now()
                ]);

                return ResponseHelpers::success(
                    message: "Payment For Custom ID Has Been Successfully Verified",
                    data: [
                        "payment_ref" => $custom_id->payment_ref
                    ]
                );
            }


            return ResponseHelpers::error(message: $response["message"]);
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }


    static public function updateCustomId(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'customized_username' => 'required|string|max:255',
                'payment_ref' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();

            // Retreive preference with 
            $preference = Preference::where('user_id', $request->input('user_id'))
                ->first();

            //Check if prefeeence exists
            if (empty($preference)) {
                return ResponseHelpers::notfound(
                    message: 'User Preferences Settings not found'
                );
            }

            // Retrieve custom ID with specific preference ID and payment reference, and payment status 'processed'
            $custom_id = $preference->custom_id()
                ->where('preference_id', $preference->id)
                ->where('payment_ref', $request->input('payment_ref'))
                ->where('payment_status', "processed")
                ->first();

            $active_custom_id = $preference->custom_id()
                ->where('status', "active")
                ->first();



            if (!$custom_id) {
                return ResponseHelpers::error(
                    message: 'Payment for Custom ID has not been completed.'
                );
            }

            if ($active_custom_id) {
                $active_custom_id->update([
                    "status" => "invalid"
                ]);
            }

            // Update custom ID with customized username and status 'active'
            $custom_id->update([
                "customized_username" => $request->input('customized_username'),
                "status" => "active"
            ]);

            return ResponseHelpers::success(
                message: 'User Custon ID updated Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'customized_username',
                    'payment_ref'
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

    static public function showCustomId(Request $request)
    {
        try {

            $status = $request->query('status') ? $request->query('status') : "active";
            $user_id = $request->route('user_id');

            // Retrieve user preferences
            $preference = Preference::where('user_id', $user_id)
                ->first();

            if (!$preference) {
                $custom_id = CustomId::where(function ($query) use ($user_id) {
                    $query->where("customized_username", $user_id)
                        ->orWhere("customized_username", '@' . $user_id);
                })
                    ->where('status', $status)
                    ->first();

                if ($custom_id) {
                    return ResponseHelpers::sendResponse(
                        data: $custom_id->load('preference')->toArray()
                    );
                }
            }

            if (empty($preference)) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User Preferences Settings Not Found'
                );
            }

            // Get custom ID
            $custom_id = $preference->custom_id()
                ->where('preference_id',  $preference->id)
                ->where('status', $status)
                ->first();

            if (!$custom_id) {
                return ResponseHelpers::success(
                    message: 'Custom ID Not Found.',
                    data: []
                );
            }

            return ResponseHelpers::sendResponse(
                data: $custom_id->load('preference')->toArray()
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }





    /// User Verification

    static public function showVerificationStatus(Request $request)
    {
        try {

            $user_id = $request->route('user_id');
            $status = $request->query('status');
            $perPage = (int) $request->query("per_page", 15);
            $page = (int) $request->query("page", 1);

            if ($user_id) {
                // Retrieve user preferences
                $preference = Preference::where('user_id', $request->route('user_id'))
                    ->first();

                if (empty($preference)) {
                    return ResponseHelpers::sendResponse(
                        status: false,
                        statusCode: 404,
                        message: 'User Preferences Settings Not Found'
                    );
                }

                // Get verification
                $verification = $preference->verification()
                    ->where('preference_id',  $preference->id)
                    ->where('status', "active")
                    ->first();

                $payment_status = ["pending", "processed", null];


                $active_verification = $preference->verification()
                    ->where('preference_id',  $preference->id)
                    ->whereIn('payment_status', $payment_status)
                    ->first();



                if (!$verification && $active_verification) {
                    return ResponseHelpers::success(
                        message: 'User is not Verified.',
                        data: $active_verification->toArray() ?? []
                    );
                } elseif (!$verification) {
                    return ResponseHelpers::success(
                        message: 'User is not Verified.',
                        data: []
                    );
                }

                return ResponseHelpers::sendResponse(
                    data: $verification->toArray()
                );
            } else {
                $verifications = $status
                    ? Verifications::where('status', $status)
                    ->paginate($perPage, ["*"], "page", $page)
                    : Verifications::paginate($perPage, ["*"], "page", $page);

                if ($verifications->isEmpty()) {
                    return ResponseHelpers::success(
                        message: 'No User Verifications found.',
                        data: []
                    );
                }

                // Use the map function to make `created_at` visible in each item of the collection
                $verifications->getCollection()->transform(function ($verification) {

                    $preference = $verification->preference;

                    $user_details = [];

                    $user_details = AuthImpl::getUserDetails($preference->user_id);

                    $custom_id = CustomId::where('preference_id', $preference->id)->where('status', "active")->first();

                    if (!empty($user_details)) {
                        $user_details['id'] = $preference->user_id;
                        $user_details['custom_id'] = isset($custom_id->customized_username) ? $custom_id->customized_username : null;
                    }

                    $verification->user = $user_details ?? null;
                    $verification->makeVisible('created_at');

                    unset($verification->preference);

                    return $verification;
                });

                return ResponseHelpers::success(
                    data: $verifications
                );
            }
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function UploadVerificationFiles(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "media_files" => "required",
                "media_files.*" => "required|max:209715200",
                'user_id' => 'required',
                'payment_ref' => 'required',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $preference = Preference::where('user_id', $request->input('user_id'))->first();

            if (!$preference) {

                // Return error response if preference does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User preference not found'
                );
            }

            // Retrieve Verification with specific preference ID and payment reference, and payment status 'processed'
            $verification = $preference->verification()
                ->where('preference_id', $preference->id)
                ->where('payment_ref', $request->input('payment_ref'))
                ->where('payment_status', "processed")
                ->first();

            $active_verification = $preference->verification()
                ->where('status', "active")
                ->first();

            if (!$verification) {
                return ResponseHelpers::error(
                    message: 'Payment for User Verification has not been completed.'
                );
            }

            // Upload File
            $fileData = AuthImpl::uploadAsset(
                $request->media_files,
                "verification-asset"
            );

            // return $fileData;

            if ($fileData["status"]) {

                if ($active_verification) {
                    $active_verification->update([
                        "status" => "inactive"
                    ]);
                }

                // Update Verification with status 'pending'
                $verification->update([
                    "status" => "pending",
                    "gid_status" => "pending",
                    "file" => $fileData["data"]["group_id"],
                ]);

                return ResponseHelpers::sendResponse(message: 'User Verified Successfully');
            }

            return ResponseHelpers::error("Unable to upload Assets");
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'payment_ref',
                    'media_files'
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

    static public function initializeVerificationPayment(Request $request)
    {
        try {

            $amount = PaymentAmount::where("type", "account_verification")->first()->amount;

            $user_id = $request->input('user_id');

            $request->merge(['amount' => strval($amount)]);

            $user_info = AuthImpl::getUserDetails($user_id);

            if (isset($user_info['country']) && $user_info['country'] === "NG") {
                $request->merge(['currency' => "NGN"]);
                $exchanger = new CurrencyConverter();


                $amount = $exchanger->convert($amount, "USD",  "NGN");

                $request->merge(['amount' => strval(round($amount, 2))]);
            } else if (isset($user_info['country']) && $user_info['country'] !== "NG") {
                $request->merge(['currency' => "USD"]);
            }

            // Retrieve preference by user ID
            $preference = Preference::where('user_id', $user_id)->first();

            if (!$preference) {
                return ResponseHelpers::notFound(
                    message: 'User Preferences Settings not found'
                );
            }

            // Check if the last Verification change was within the last year
            $last_verification = $preference->verification()->where('preference_id', $preference->id)
                ->where('status', "active")->first();

            if ($last_verification && Carbon::parse($last_verification->created_at)->gt(Carbon::now()->subYear())) {
                return ResponseHelpers::error(
                    message: 'You can only verify an account once every year.'
                );
            }

            $pending_paid_verification = $preference->verification()->where('preference_id', $preference->id)
                ->where('status', "pending")
                ->where('payment_status', 'processed')
                ->first();

            if ($pending_paid_verification) {
                return ResponseHelpers::success(
                    message: 'You have a processed payment with pending document verification',
                    data: $pending_paid_verification->toArray() ?? []
                );
            }

            // Check for any existing Verification with payment status 'pending'
            $verification = $preference->verification()->where('preference_id', $preference->id)
                ->where('payment_status', 'pending')
                ->first();

            if ($verification) {
                $checkResponse = self::verifyUserVerificationPayment($request, $verification->payment_ref);

                $check = $checkResponse->getData(true);


                if ($check['status'] && isset($check['data']['payment_ref'])) {
                    $verification->update([
                        "payment_status" => "processed",
                        "payment_verified_at" => Carbon::now(),
                    ]);


                    return ResponseHelpers::success(
                        message: "Payment For User Verification Found and Verified",
                        data: [
                            "payment_ref" => $verification->payment_ref
                        ]
                    );
                }
            }


            // Initialize payment process
            $response = PaymentImpl::initializePayment($request);

            if (!isset($response['payment_reference'])) {
                return ResponseHelpers::error(
                    message: "Error intializing payment: {$response}"
                );
            }


            if (empty($verification)) {

                // If no pending Verification, create a new one with payment status 'Pending'
                $preference->verification()->create([
                    "status" => null,
                    "payment_status" => "pending",
                    "payment_ref" => $response['payment_reference'],
                    "payment_initialized_at" => Carbon::now(),
                    "file" => null,
                    "gid_status" => null,
                ]);

                return ResponseHelpers::success(
                    message: 'Payment For User Verification Has Been Successfully Initiated.',
                    data: $response
                );
            }

            // If a pending Verification exists, update the payment reference
            $verification->update([
                "payment_ref" => $response['payment_reference'],
            ]);

            return ResponseHelpers::success(
                message: 'Payment For Verification Has Been Successfully Initiated.',
                data: $response
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function verifyUserVerificationPayment(Request $request, string $payment_ref = null)
    {
        try {

            $payment_reference = $payment_ref ? $payment_ref : $request->query('payment_ref');

            // Check if payment reference is provided
            if (!$payment_reference) {
                return ResponseHelpers::error(message: 'No Payment Reference Inputed');
            }

            // Retrieve User Verification based on payment reference
            $verification = Verifications::where('payment_ref', $payment_reference)->first();

            if (!$verification) {
                return ResponseHelpers::notFound(message: 'Payment For User Verification Not Found');
            }


            // Check if payment status is already processed
            if ($verification->payment_status === "processed") {
                return ResponseHelpers::success(
                    message: "Payment For User Verification Has Been Successfully Verified",
                    data: [
                        "payment_ref" => $verification->payment_ref
                    ]
                );
            }

            //Verify payment
            $response = PaymentImpl::verifyPayment($payment_reference);

            // If payment verification is successful, update payment status
            if ($response['status'] === "success") {
                $verification->update([
                    "payment_status" => "processed",
                    "payment_verified_at" => Carbon::now()
                ]);

                return ResponseHelpers::success(
                    message: "Payment For User Verification was Successful",
                    data: [
                        "payment_ref" => $verification->payment_ref
                    ]
                );
            }


            return ResponseHelpers::error(message: $response["message"]);
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    static public function IphoneUserVerificationPayment(Request $request)
    {
        try {

            $user_id = $request->input('user_id');

            // Retrieve preference by user ID
            $preference = Preference::where('user_id', $user_id)->first();

            if (!$preference) {
                return ResponseHelpers::notFound(
                    message: 'User Preferences Settings not found'
                );
            }

            $last_verification = Verifications::where('preference_id', $preference->id)
                ->whereNull('status')
                ->where('payment_ref', "IPHONE_VERIFICATION")
                ->whereNull("gid_status")
                ->first();

            if ($last_verification) {
                return ResponseHelpers::success(
                    message: "Payment For User Verification was Successful"
                );
            }

            $verification = $preference->verification();
            // If no pending Verification, create a new one with payment status 'Pending'
            $verification->create([
                "status" => null,
                "payment_status" =>  "processed",
                "payment_ref" =>  "IPHONE_VERIFICATION",
                "payment_initialized_at" => Carbon::now(),
                "file" => null,
                "gid_status" => null,
            ]);


            return ResponseHelpers::success(
                message: "Payment For User Verification was Successful"
            );



            return ResponseHelpers::error(message: $response["message"]);
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }

    public static function getStats(Request $request)
    {
        $subscribed_users = Preference::where('subscribed', true)->count();

        $verified_users = Verifications::where('payment_status', 'processed')->where('status', 'active')->count();

        return ResponseHelpers::success(data: [
            "subscribed_users" => $subscribed_users,
            "verified_users" => $verified_users
        ]);
    }


    public static function getPendingGovernmentIssuedID(Request $request)
    {

        $status = $request->query('sort');

        if (!$status) {
            return ResponseHelpers::error(
                message: "Add Verification Status"
            );
        }

        $pending_verification = Verifications::where('gid_status', $status)->get();

        if (!$pending_verification) {
            return ResponseHelpers::notFound(
                message: "No Verification found"
            );
        }


        $pending_verification->map(function ($verification) {
            $verification->user_id = $verification->preference->user_id;
            unset($verification->preference);
        });

        return ResponseHelpers::success(
            data: $pending_verification->toArray()
        );
    }


    public static function updateGovernmentIssuedIdStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'verification_id' => 'required|integer',
                'gid_status' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $pending_gid = Verifications::where('id', $request->verification_id)->first();

            if (!$pending_gid) {
                return ResponseHelpers::notFound(
                    message: "No Verification found"
                );
            }

            $gid_status = $request->input('gid_status');

            $pending_gid->update([
                'gid_status' => $gid_status,
                'status' =>  $gid_status === "approved" ? "active" : "pending"
            ]);


            if ($gid_status === "approved") {
                $pending_gid->preference->update([
                    "is_verified" => true
                ]);
            }

            return ResponseHelpers::success(
                message: "Government Issued ID Status Updated Successfully"
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'verification_id',
                    'gid_status'
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


    public static function searchCustomIds(Request $request)
    {
        try {

            $searchParam = $request->query('search');

            $searchParam = strtolower($searchParam);

            // Retrieve custom IDs similar to the search parameter with an 'active' status
            $customIds = CustomId::with('preference')->where('status', 'active')
                ->whereRaw('LOWER(customized_username) LIKE ?', ['%' . $searchParam . '%'])
                ->get();

            if ($customIds->isEmpty()) {
                return ResponseHelpers::error(
                    message: 'No matching custom IDs found'
                );
            }

            // Add user info to each profile item
            //  $customIds->each(function ($custom_id) {
            //     $custom_id->user_id = $custom_id->preference->user_id;
            // });

            return ResponseHelpers::sendResponse(data: $customIds->toArray());
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
}
