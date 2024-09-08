<?php

namespace App\Http\Controllers;

use App\Services\FCMTokenService;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function storeFcmToken(Request $request)
    {
        return FCMTokenService::storeFcmToken($request);
    }

    public function removeFcmToken(Request $request)
    {
        return FCMTokenService::removeFcmToken($request);
    }
}

