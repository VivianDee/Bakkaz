<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelpers;
use App\Models\Preference;
use App\Services\LanguageService;
use App\Services\PreferenceService;

use Illuminate\Http\Request;

class PreferenceController extends Controller
{

    /// Preferences
    public function showPreference(Request $request)
    {
        return PreferenceService::showPreference($request);
    }

    public function createPreference(Request $request)
    {
        return PreferenceService::createPreference($request);
    }

    public function updatePreference(Request $request)
    {
        return PreferenceService::updatePreference($request);
    }

    public function deletePreference(Request $request)
    {
        return PreferenceService::deletePreference($request);
    }

    public function restorePreference(Request $request)
    {
        return PreferenceService::restorePreference($request);
    }


    // Verification

    public function initializeVerificationPayment(Request $request)
    {
        return PreferenceService::initializeVerificationPayment($request);
    }

    public function verifyUserVerificationPayment(Request $request)
    {
        return PreferenceService::verifyUserVerificationPayment($request);
    }

    public function UploadVerificationFiles(Request $request)
    {
        return PreferenceService::UploadVerificationFiles($request);
    }

    public function showVerificationStatus(Request $request)
    {
        return PreferenceService::showVerificationStatus($request);
    }

    public static function updateGovernmentIssuedIdStatus(Request $request)
    {
        return PreferenceService::updateGovernmentIssuedIdStatus($request);
    }

    public function getStats(Request $request)
    {
        return PreferenceService::getStats($request);
    }

    public function getPendingGovernmentIssuedID(Request $request)
    {
        return PreferenceService::getPendingGovernmentIssuedID($request);
    }



    /// Subscription
    public function Issuscribed(Request $request)
    {
        return PreferenceService::Issuscribed($request);
    }

    public function updateSubscription(Request $request)
    {
        return PreferenceService::updateSubscription($request);
    }





    /// Language
    public function updateLanguage(Request $request)
    {
        return PreferenceService::updateLanguage($request);
    }

    public function createLanguage(Request $request)
    {
        return LanguageService::createLanguage($request);
    }

    public function showLanguages(Request $request)
    {
        return LanguageService::showLanguages($request);
    }




    /// Custom ID

    public function initializeCustomIdPayment(Request $request)
    {
        return PreferenceService::initializeCustomIdPayment($request);
    }

    public function verifyCustomIdPayment(Request $request)
    {
        return PreferenceService::verifyCustomIdPayment($request);
    }

    public function IphoneUserVerificationPayment(Request $request)
    {
        return PreferenceService::IphoneUserVerificationPayment($request);
    }

    public function updateCustomId(Request $request)
    {
        return PreferenceService::updateCustomId($request);
    }

    public function showCustomId(Request $request)
    {
        return PreferenceService::showCustomId($request);
    }

    public function searchCustomIds(Request $request)
    {
        return PreferenceService::searchCustomIds($request);
    }
}
