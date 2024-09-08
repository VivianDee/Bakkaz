<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface AuthInterface
{
    /// login
    public static function login(Request $request);

    /// register
    public static function register(Request $request);
    public static function verifyAccount(Request $request);

    /// recover password
    public static function sendOtp(Request $request);
    public static function changePassword(Request $request);

    /// logout
    public static function logout(Request $request);

    /// auth_info

    public static function saveDevice(Request $request);
    public static function savePresentLocation(Request $request);

    public static function refresh(Request $request);
}
