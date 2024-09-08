<?php
// app/Http/Controllers/SubscriptionController.php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelpers;
use App\Models\Plan;
use App\Models\Subscription;
use App\Service\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        return SubscriptionService::initializeSubscription($request);
    }

    public function verifyPayment(Request $request)
    {
        return SubscriptionService::verifyPayment($request);
    }

    public function unsubscribe(Request $request)
    {
        return SubscriptionService::unsubscribe($request);
    }

    public function changePlan(Request $request)
    {
        return SubscriptionService::changePlan($request);
    }
    public function getCurrentSubscriptionAndPlan(Request $request)
    {
        return SubscriptionService::getCurrentSubscriptionAndPlan($request);
    }
}
