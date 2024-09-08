<?php

namespace App\Http\Controllers;

use App\Services\AdminAuthService;
use App\Services\GoogleServices;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    /// login
    public static function login(Request $request)
    {
        return AdminAuthService::login($request);
    }
    public static function redirectToGoogle(Request $request)
    {
        return GoogleServices::redirectToGoogle();
    }
    public static function handleGoogleCallback(Request $request)
    {
        return GoogleServices::handleGoogleCallback($request);
    }

    /// register
    public static function register(Request $request)
    {
        return AdminAuthService::register($request);
    }
    public static function verifyAccount(Request $request)
    {
        return AdminAuthService::verifyAccount($request);
    }

    /// recover password
    public static function sendOtp(Request $request)
    {
        return AdminAuthService::sendOtp($request);
    }

    public static function verifyOtp(Request $request)
    {
        return AdminAuthService::verifyOtp($request);
    }

    public static function changePassword(Request $request)
    {
        return AdminAuthService::changePassword($request);
    }

    /// logout
    public static function logout(Request $request)
    {
        return AdminAuthService::logout($request);
    }

    /// session
    public static function refresh(Request $request)
    {
        return AdminAuthService::refresh($request);
    }

    public static function saveDevice(Request $request)
    {
        return AdminAuthService::saveDevice($request);
    }
    public static function savePresentLocation(Request $request)
    {
        return AdminAuthService::savePresentLocation($request);
    }
    public static function savePasswordHistory(Request $request)
    {
        return AdminAuthService::savePasswordHistory($request);
    }
    public static function saveLoginHistory(Request $request)
    {
        return AdminAuthService::saveLoginHistory($request);
    }
}
