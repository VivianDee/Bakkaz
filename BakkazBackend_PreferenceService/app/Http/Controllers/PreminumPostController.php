<?php

namespace App\Http\Controllers;

use App\Services\PremiumPostService;
use Illuminate\Http\Request;

class PreminumPostController extends Controller
{
    public function initializePremiumPostPayment(Request $request)
    {
        return PremiumPostService::initializePremiumPostPayment($request);
    }

    public function verifyPremiumPostPayment(Request $request)
    {
        return PremiumPostService::verifyPremiumPostPayment($request);
    }

    public function updatePremiumPost(Request $request)
    {
        return PremiumPostService::updatePremiumPost($request);
    }

    public function showPremiumPost(Request $request)
    {
        return PremiumPostService::showPremiumPost($request);
    }
}
