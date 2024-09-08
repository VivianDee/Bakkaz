<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    
    public function changePassword(Request $request)
    {
        return SettingsService::changePassword($request);
    }

}
