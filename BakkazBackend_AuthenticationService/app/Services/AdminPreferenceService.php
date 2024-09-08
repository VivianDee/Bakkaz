<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use App\Models\AdminPreference;
use App\Models\AdminPermission;
use App\Models\Permission;
use Illuminate\Http\Request;

class AdminPreferenceService
{
    /**
     * Get all preferences.
     */
    public static function getAllPreferences()
    {
        $preferences = AdminPreference::with('permission')->get();

        if ($preferences->isEmpty()) {
            return ResponseHelpers::notFound('No preferences found.');
        }

        return ResponseHelpers::success($preferences);
    }

    /**
     * Assign a preference to an admin.
     */
    public static function assignPreferenceToAdmin(Request $request)
    {
        $admin_id = $request->input('admin_id');
        $preference_status = $request->input('preference_status', true); // Default to true if not provided

        // Validate if the admin_id is provided
        if (!$admin_id) {
            return ResponseHelpers::error(message: "Admin ID is required.");
        }

        // Find or create a permission for the admin
        $permission = Permission::updateOrCreate(
            ['admin_id' => $admin_id],
            ['admin_id' => $admin_id]
        );

        // Check if the permission was created successfully
        if (!$permission->id) {
            return ResponseHelpers::error(message: "Failed to create or update permission.");
        }

        // Find or create the admin preference
        $admin_preference = AdminPreference::updateOrCreate(
            [
                'admin_id' => $admin_id
            ],
            [
                'permissions_id' => $permission->id,
                'preference_status' => $preference_status
            ]
        );

        return ResponseHelpers::success(message: 'Preference assigned successfully.', data: $admin_preference);
    }


    /**
     * Remove a preference from an admin.
     */
    public static function removePreferenceFromAdmin(Request $request)
    {
        $admin_id = $request->route('admin_id');

        // Validate if the admin_id is provided
        if (!$admin_id) {
            return ResponseHelpers::error(message: "Admin ID is required.");
        }

        // Find the admin preference for the admin
        $preference = AdminPreference::where('admin_id', $admin_id)->first();

        if (!$preference) {
            return ResponseHelpers::notFound('Preference not found for this admin.');
        }

        $preference->delete();

        return ResponseHelpers::success(message: 'Preference removed successfully.');
    }


    /**
     * Get preferences for an admin.
     */
    public static function getAdminPreferences($admin_id)
    {
        $preferences = AdminPreference::where('admin_id', $admin_id)
            ->with('permission') // Assuming AdminPreference has a relationship with Permission
            ->get();

        if ($preferences->isEmpty()) {
            return ResponseHelpers::notFound('No preferences found for this admin.');
        }

        return ResponseHelpers::success($preferences);
    }
}
