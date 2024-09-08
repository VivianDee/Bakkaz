<?php

namespace App\Http\Controllers;

use App\Services\AdminPreferenceService;
use Illuminate\Http\Request;

class AdminPreferenceController extends Controller
{
    /**
     * Assign a preference to an admin.
     */
    public function assignPreferenceToAdmin(Request $request)
    {
        return AdminPreferenceService::assignPreferenceToAdmin($request);
    }

    /**
     * Remove a preference from an admin.
     */
    public function removePreferenceFromAdmin(Request $request)
    {
        return AdminPreferenceService::removePreferenceFromAdmin($request);
    }

    /**
     * Get all preferences for a specific admin.
     */
    public function getAdminPreferences($admin_id)
    {
        return AdminPreferenceService::getAdminPreferences($admin_id);
    }

    /**
     * Get all preferences.
     */
    public function getAllPreferences()
    {
        return AdminPreferenceService::getAllPreferences();
    }
}
