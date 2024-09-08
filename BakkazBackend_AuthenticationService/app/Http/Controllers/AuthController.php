<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TokenLife;
use App\Services\AuthService;
use App\Services\GoogleServices;
use App\Services\TokenLifeService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
     /// verifiction Status
    public static function getVerificationStatus(Request $request)
    {
        return AuthService::getVerificationStatus($request);
    }

    /// login
    public static function login(Request $request)
    {
        return AuthService::login($request);
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
        return AuthService::register($request);
    }
    public static function verifyAccount(Request $request)
    {
        return AuthService::verifyAccount($request);
    }

    /// recover password
    public static function sendOtp(Request $request)
    {
        return AuthService::sendOtp($request);
    }

    public static function verifyOtp(Request $request)
    {
        return AuthService::verifyOtp($request);
    }

    public static function changePassword(Request $request)
    {
        return AuthService::changePassword($request);
    }

    /// logout
    public static function logout(Request $request)
    {
        return AuthService::logout($request);
    }

    /// session
    public static function refresh(Request $request)
    {
        return AuthService::refresh($request);
    }

    public static function saveDevice(Request $request)
    {
        return AuthService::saveDevice($request);
    }
    public static function savePresentLocation(Request $request)
    {
        return AuthService::savePresentLocation($request);
    }
    public static function savePasswordHistory(Request $request)
    {
        return AuthService::savePasswordHistory($request);
    }
    public static function saveLoginHistory(Request $request)
    {
        return AuthService::saveLoginHistory($request);
    }
}
