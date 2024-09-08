<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Impl\CurrencyConverter;
use App\Impl\Services\AuthImpl;
use App\Impl\Services\PaymentImpl;
use App\Models\Preference;
use App\Models\PremiumPost;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class PremiumPostService
{
    /// Premiun Posts

    static public function initializePremiumPostPayment(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $amount = 1;

            $request->merge(['amount' => strval(1)]);

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


            // Check for any existing Premium Post with payment status 'pending'
            $premium_post = $preference->premium_post()->where('preference_id', $preference->id)
                ->where('payment_status', 'pending')
                ->first();

            if ($premium_post) {
                $checkResponse = self::verifyPremiumPostPayment($request, $premium_post->payment_ref, $premium_post->user_id);

                $check = $checkResponse->getData(true);


                if ($check['status'] && isset($check['data']['payment_ref'])) {
                    $premium_post->update([
                        "payment_status" => "processed",
                        "payment_verified_at" => Carbon::now(),
                    ]);


                    return ResponseHelpers::success(
                        message: "Payment For Premium Post Found and Verified",
                        data: [
                            "payment_ref" => $premium_post->payment_ref
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

            if (empty($premium_post)) {

                // If no pending Premium Post, create a new one with payment status 'pending'
                $preference->premium_post()->create([
                    "user_id" => $user_id,
                    "status" => "pending",
                    "payment_status" => "pending",
                    "payment_ref" => $response['payment_reference'],
                    "payment_initialized_at" => Carbon::now()
                ]);

                return ResponseHelpers::success(
                    message: 'Payment For Premium Post Has Been Successfully Initiated.',
                    data: $response
                );
            }

            // If a pending Premium Post exists, update the payment reference
            $premium_post->update([
                "payment_ref" => $response['payment_reference'],
            ]);

            return ResponseHelpers::success(
                message: 'Payment For Premium Post Has Been Successfully Initiated.',
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

    static public function verifyPremiumPostPayment(Request $request, $payment_ref = null, $user_id = null, $mode = null)
    {
        try {

            $payment_reference = $payment_ref ? $payment_ref : $request->query('payment_ref');
            $user_id = $user_id ? $user_id : $request->query('user_id');
            // $mode = $mode ? $mode : $request->query('mode');

            // Check if payment reference is provided
            if (!$payment_reference) {
                return ResponseHelpers::error(message: 'No Payment Reference Inputed');
            }

            // Retrieve Premium Post based on payment reference
            $premium_post = PremiumPost::where('payment_ref', $payment_reference)
            ->where('user_id', $user_id)
            ->first();

            if (!$premium_post) {
                return ResponseHelpers::notFound(message: 'Payment For Premium Post Not Found');
            }


            // Check if payment status is already processed
            if ($premium_post->payment_status === "processed") {
                return ResponseHelpers::success(
                    message: "Payment For Premium Post Has Been Successfully Verified",
                    data: [
                        "payment_ref" => $premium_post->payment_ref
                    ]
                );
            }

            //Verify payment
            $response = PaymentImpl::verifyPayment($payment_reference);

            // If payment verification is successful, update payment status
            if ($response['status'] === "success") {
                $premium_post->update([
                    'status' => "active",
                    "payment_status" => "processed",
                    "payment_verified_at" => Carbon::now()
                ]);

                return ResponseHelpers::success(
                    message: "Payment For Premium Post Has Been Successfully Verified",
                    data: [
                        "payment_ref" => $premium_post->payment_ref
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

    static public function updatePremiumPost(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
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

            // Retrieve Premium Post with specific preference ID and payment reference, and payment status 'processed'
            $premium_post = $preference->premium_post()
                ->where('preference_id', $preference->id)
                ->where('payment_ref', $request->input('payment_ref'))
                ->where('payment_status', "processed")
                ->first();



            if (!$premium_post) {
                return ResponseHelpers::error(
                    message: 'Payment for Premium Post has not been completed.'
                );
            }

            // Update Premium Post with status 'active'
            $premium_post->update([
                "status" => "invalid"
            ]);

            return ResponseHelpers::success(
                message: 'Premium Post Subscription Invalidated Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
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

    static public function showPremiumPost(Request $request)
    {
        try {

            $status = $request->query('status') ? array($request->query('status')) : ["active", "pending"];

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

            // Get Premium Post
            $premium_post = $preference->premium_post()
                ->where('preference_id',  $preference->id)
                ->whereIn('status', $status)
                ->first();

            // Determine the file upload size limit
            $file_upload_size = 60;
            $premium_upload_size = 500;

            if (!$premium_post) {
                return ResponseHelpers::success(
                    message: 'Premium Post Subscription Not Found.',
                    data: [
                        'status' => 'uninitialized',
                        'file_upload_size' => $file_upload_size,
                        'premium_upload_size' => null,
                    ]
                );
            }

            // Include file_upload_size in the response
            $response_data = $premium_post->toArray();
            $response_data['file_upload_size'] = $file_upload_size;
            $response_data['premium_upload_size'] = $premium_post->status === "active" ? $premium_upload_size : null;


            return ResponseHelpers::sendResponse(
                data: $response_data
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
