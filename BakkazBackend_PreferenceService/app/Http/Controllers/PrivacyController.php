<?php

namespace App\Http\Controllers;

use App\Services\BlockUsersService;
use App\Services\MutedUsersService;
use Illuminate\Http\Request;
use App\Services\PrivacyService;
use App\Services\ReportUsersService;

class PrivacyController extends Controller
{

    ///Privacy

    public function updatePrivacySettings(Request $request)
    {
        return PrivacyService::updatePrivacySettings($request);
    }

    public function showPrivacy(Request $request)
    {
        return PrivacyService::showPrivacy($request);
    }

    /// Block User

    public function showBlockedUsers(Request $request)
    {
        return BlockUsersService::showBlockedUsers($request);
    }

    public function blockUser(Request $request)
    {
        return BlockUsersService::blockUser($request);
    }


     /// Muted User

     public function showMutedUsers(Request $request)
     {
         return MutedUsersService::showMutedUsers($request);
     }
 
     public function muteUser(Request $request)
     {
         return MutedUsersService::muteUser($request);
     }

     /// Report User

    public function ReportUser(Request $request)
    {
        return ReportUsersService::ReportUser($request);
    }

    public function showReportedUsers(Request $request)
    {
        return ReportUsersService::showReportedUsers($request);
    }

    // 
    public function NonMentionables(Request $request) {
        return PrivacyService::NonMentionables($request);
    }

    
    public function showPrivacySettingsByIDs(Request $request) {
        return PrivacyService::showPrivacySettingsByIDs($request);
    }
    
}
