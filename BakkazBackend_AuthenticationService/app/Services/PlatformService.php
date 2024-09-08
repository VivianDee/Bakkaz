<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use App\Models\AdminPlatform;
use App\Models\Platform;
use Illuminate\Http\Request;

class PlatformService
{
    public static function addPlatformToAdminPlatforms(Request $request)
    {
        $admin_id = $request->route('admin_id');
        $platform_id = $request->route('platform_id');

        $admin_platform = AdminPlatform::create([
            'admin_id' => $admin_id,
            'platform_id' => $platform_id,
        ]);

        if (!$admin_platform) {
            return ResponseHelpers::error(message: 'Unable to add admin to platform');
        }

        return ResponseHelpers::success($admin_platform);
    }

    public static function removePlatformFromAdminPlatforms(Request $request)
    {
        $admin_id = $request->route('admin_id');
        $platform_id = $request->route('platform_id');

        $admin_platform = AdminPlatform::where('admin_id', $admin_id)
                                       ->where('platform_id', $platform_id)
                                       ->delete();

        if ($admin_platform) {
            return ResponseHelpers::success(message: 'Admin removed from platform successfully');
        }

        return ResponseHelpers::error(message: 'Unable to remove admin from platform');
    }

    public static function createPlatform(Request $request)
    {
        $platform = Platform::create($request->only(['name', 'description', 'meta_data','short_name']));

        if (!$platform) {
            return ResponseHelpers::error(message: 'Unable to create platform');
        }

        return ResponseHelpers::success($platform);
    }

    public static function deletePlatform(Request $request)
    {
        $platform_id = $request->route('platform_id');
        $platform = Platform::find($platform_id);

        if (!$platform) {
            return ResponseHelpers::error(message: 'Platform not found');
        }

        $platform->delete();

        return ResponseHelpers::success(message: 'Platform deleted successfully');
    }

    public static function updatePlatform(Request $request)
    {
        $platform_id = $request->route('platform_id');
        $platform = Platform::find($platform_id);

        if (!$platform) {
            return ResponseHelpers::error(message: 'Platform not found');
        }

        $platform->update($request->only(['name', 'description', 'meta_data']));

        return ResponseHelpers::success($platform);
    }

    public static function getAdminPlatform(Request $request)
    {
        $admin_id = $request->route('admin_id');

        $platforms = AdminPlatform::where('admin_id', $admin_id)->get();

        if ($platforms->isEmpty()) {
            return ResponseHelpers::success(message: 'No platforms found for admin. Contact super admin to request permissions.');
        }

        return ResponseHelpers::success($platforms);
    }

    public static function getPlatform(Request $request)
    {
        $platforms = Platform::all();

        if ($platforms->isEmpty()) {
            return ResponseHelpers::notFound('No platforms configured yet');
        }

        return ResponseHelpers::success($platforms);
    }
}
