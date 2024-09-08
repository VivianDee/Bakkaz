<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface PrivacyInterface
{
    // Privacy
    static public function updatePrivacySettings(Request $request);

    static public function showPrivacy(Request $request);

    static public function NonMentionables(Request $request);

    static public function showPrivacySettingsByIDs(Request $request);
}
