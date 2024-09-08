<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface SecurityInterface
{
    // Security
    static public function showSecurity(Request $request);
    static public function updateSecuritySettings(Request $request);
}
