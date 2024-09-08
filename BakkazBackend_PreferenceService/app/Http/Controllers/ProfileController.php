<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProfileService;

class ProfileController extends Controller
{


    /// Profile

    public function showProfile(Request $request)
    {
        return ProfileService::showProfile($request);
    }

    public function updateUserProfile(Request $request)
    {
        return ProfileService::updateUserProfile($request);
    }
}
