<?php

namespace App\Http\Controllers;

use App\Services\SecurityService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    
    /// Security

    public function showSecurity(Request $request)
    {
        return SecurityService::showSecurity($request);
    }

    public function updateSecuritySettings(Request $request)
    {
        return SecurityService::updateSecuritySettings($request);
    }
}
