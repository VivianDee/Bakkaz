<?php

namespace App\Http\Controllers;

use App\Services\PlatformService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlatformController extends Controller
{
    public function addPlatformToAdminPlatforms(Request $request, $admin_id, $platform_id)
    {
        return PlatformService::addPlatformToAdminPlatforms($request);
    }

    public function removePlatformFromAdminPlatforms(Request $request, $admin_id, $platform_id)
    {
        return PlatformService::removePlatformFromAdminPlatforms($request);
    }

    public function createPlatform(Request $request)
    {
        return PlatformService::createPlatform($request);
    }

    public function deletePlatform(Request $request, $platform_id)
    {
        return PlatformService::deletePlatform($request);
    }

    public function updatePlatform(Request $request, $platform_id)
    {
        return PlatformService::updatePlatform($request);
    }

    public function getAdminPlatform(Request $request, $admin_id)
    {
        return PlatformService::getAdminPlatform($request);
    }

    public function getPlatform(Request $request)
    {
        return PlatformService::getPlatform($request);
    }
}
