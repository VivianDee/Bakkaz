<?php

namespace App\Http\Controllers;

use App\Models\PaymentAmount;
use App\Services\PaymentAmountService;
use Illuminate\Http\Request;

class PaymentAmountController extends Controller
{
    
    /// PaymentAmount
    public function createPaymentAmount(Request $request)
    {
        return PaymentAmountService::createPaymentAmount($request);
    }

    public function showPaymentAmounts(Request $request)
    {
        return PaymentAmountService::showPaymentAmounts($request);
    }

    public function updatePaymentAmount(Request $request)
    {
        return PaymentAmountService::updatePaymentAmount($request);
    }
}
