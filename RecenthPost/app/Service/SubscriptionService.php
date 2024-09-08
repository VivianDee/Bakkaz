<?php

namespace App\Service;

use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Impl\Services\PaymentImpl;
use App\Impl\Services\PreferenceImpl;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public static function initializeSubscription(Request $request)
    {
        self::validateInitializeRequest($request);

        $activeSub = self::checkIfSubIsActive($request->user_id, 'active');
        if ($activeSub) {
            return ResponseHelpers::unprocessableEntity(
                "You already have an active subscription. Please unsubscribe to upgrade or downgrade."
            );
        }

        $pendingSub = self::checkIfSubIsActive($request->user_id, 'pending');

        DB::beginTransaction();

        try {
            if ($pendingSub) {
                $subscription = $pendingSub;
                $message = "Pending Subscription Found, Re-initialized";
            } else {
                $subscription = Subscription::create([
                    'user_id' => $request->user_id,
                    'plan_id' => $request->plan_id,
                    'status' => 'pending',
                ]);
                $message = "Subscription initialized";
            }

            $request->merge(['amount' => Plan::find($request->plan_id)->price]);
            // $request->merge(['amount' => '10000']);
            $paymentInfo = PaymentImpl::initialzePayment($request);
            if ($paymentInfo['status']) {
                $subscription->update([
                    'payment_ref' => $paymentInfo['data']['payment_reference'],
                    'payment_initialized_at' => Carbon::now(),
                ]);
                DB::commit();
                return ResponseHelpers::success([
                    'subscription' => $subscription,
                    'payment_info' => $paymentInfo['data'],
                ], $message);
            } else {
                DB::rollBack();
                return ResponseHelpers::error("Payment Initialization Failed");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function verifyPayment(Request $request)
    {
        $subscription = Subscription::where('payment_ref', $request->query('reference'))->where('status', 'pending')->first();

        if (!$subscription) {
            return ResponseHelpers::notFound("Subscription not found");
        }

        if ($subscription->status == 'active') {
            return ResponseHelpers::success($subscription, "Subscription Already Verified");
        }

        $payment = PaymentImpl::verifyPayment($request->reference);
        if ($payment['status'] != 'abandoned') {
            $subscription->update([
                'status' => 'active',
                'payment_verified_at' => Carbon::now(),
            ]);

            PreferenceImpl::updateSubscritionStatus(user_id: $subscription->user_id, status: true, subscription_id: $subscription->id);


            return ResponseHelpers::success($subscription, "Payment verified and subscription activated.");
        }

        return ResponseHelpers::error("Unable to verify payment. {$payment['message']}");
    }

    public static function checkIfSubIsActive(int $userId, string $status)
    {
        return Subscription::where('user_id', $userId)->where('status', $status)->first();
    }

    public static function unsubscribe(Request $request)
    {
        $userId = $request->route('user_id');
        $user = AuthImpl::getUserDetails($userId);

        if (!$user) {
            return ResponseHelpers::notFound("User not found");
        }

        $subscription = Subscription::where('user_id', $userId)->where('status', 'active')->first();

        if (!$subscription) {
            return ResponseHelpers::notFound("Subscription not found");
        }

        $subscription->update(['status' => 'cancelled']);

        PreferenceImpl::updateSubscritionStatus(user_id: $subscription->user_id, status: false);

        return ResponseHelpers::success($subscription, "Unsubscribed successfully");
    }

    public static function changePlan(Request $request)
    {
        $currentSubscription = self::checkIfSubIsActive($request->user_id, 'active');

        if (!$currentSubscription) {
            return ResponseHelpers::notFound("No active subscription found for user");
        }

        DB::beginTransaction();

        try {
            $currentSubscription->update(['status' => 'cancelled']);

            $newSubscription = Subscription::create([
                'user_id' => $request->user_id,
                'plan_id' => $request->plan_id,
                'status' => 'pending',
            ]);

            $currentPlan = Plan::find($currentSubscription->plan_id);
            $newPlan = Plan::find($request->plan_id);

            // Calculate the remaining value of the current subscription 
            $currentSubscriptionValue = self::calculateRemainingValue($currentSubscription, $currentPlan);


            if (floatval($newPlan->price) < floatval($currentPlan->price)) {
                return ResponseHelpers::error("Cannot switch to a lower value plan");
            }

            if (floatval($newPlan->price) === floatval($currentPlan->price)) {
                return ResponseHelpers::error("User already subscribed to this plan");
            }

             // Calculate the amount for the new plan
            $newPlanAmount = floatval($newPlan->price) - $currentSubscriptionValue;
            $newPlanAmount = max($newPlanAmount, 0);

            $request->merge(['amount' => strval($newPlanAmount)]);
            // $request->merge(['amount' => Plan::find($request->plan_id)->price]);
            // $request->merge(['amount' => '10000']);


            $paymentInfo = PaymentImpl::initialzePayment($request);
            if ($paymentInfo['status']) {
                $newSubscription->update([
                    'payment_ref' => $paymentInfo['data']['payment_reference'],
                    'payment_initialized_at' => Carbon::now(),
                ]);
                DB::commit();
                return ResponseHelpers::success([
                    'subscription' => $newSubscription,
                    'payment_info' => $paymentInfo['data'],
                ], "Upgrade initialized");
            }

            DB::rollBack();
            return ResponseHelpers::error("Payment Initialization Failed");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getCurrentSubscriptionAndPlan(Request $request)
    {
        $userId = $request->query('user_id');
        $subscription = Subscription::where('user_id', $userId)->where('status', 'active')->first();

        if ($subscription) {
            $plan = Plan::find($subscription->plan_id);
            return ResponseHelpers::success([
                'subscription' => $subscription,
                'plan' => $plan,
            ], "Active subscription and plan retrieved successfully.");
        }

        return ResponseHelpers::success([
            'subscription' => null,
            'plan' => null,
        ], "No active subscription found.");
    }

    private static function calculateRemainingValue($currentSubscription, $currentPlan)
    {
        $duration = [
            '24-hours' => 1,
            '3-days' => 3,
            '7-days' => 7,
            '14-days' => 14,
            '30-days' => 30,
            '60-days' => 60,
            '90-days' => 90,
            '180-days' => 180,
            '365-days' => 365,
        ];

        

        $start = Carbon::parse($currentSubscription->created_at);
        $end = Carbon::parse($currentSubscription->created_at)->addDays($duration[$currentPlan->duration]);
        $now = Carbon::now();

        if ($now->lt($end)) {
            $daysUsed = $start->diffInDays($now);
            $daysTotal = $start->diffInDays($end);
            $remainingValue = $currentPlan->price * (($daysTotal - $daysUsed) / $daysTotal);
        } else {
            $remainingValue = 0;
        }

        return round($remainingValue, 2);
    }


    private static function validateInitializeRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'metadata' => 'sometimes|array',
            'metadata.payment_split' => 'sometimes|array',
            'metadata.payment_split.*.app_name' => 'required_with:metadata.payment_split|string',
            'metadata.payment_split.*.percentage' => 'required_with:metadata.payment_split|numeric|min:0|max:100',
            'metadata.payment_split.*.amount' => 'required_with:metadata.payment_split|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
