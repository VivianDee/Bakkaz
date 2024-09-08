<?php

namespace App\Http\Controllers;

use App\Services\NotificationSettingsService;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function showNotificationSettings(Request $request) {
        return NotificationSettingsService::showNotificationSettings($request);
    }

    public function updateNotificationSettings(Request $request) {
        return NotificationSettingsService::updateNotificationSettings($request);
    }
}
