<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface PreferenceInterface
{

    /// Preferences 
    static public function showPreference(Request $request);
    static public function createPreference(Request $request);
    static public function updatePreference(Request $request);
    static public function restorePreference(Request $request);
    static public function deletePreference(Request $request);



    /// Subscripion
    static public function Issuscribed(Request $request);
    static public function updateSubscription(Request $request);



    ///Language 
    static public function updateLanguage(Request $request);



    ///Custom ID
    static public function initializeCustomIdPayment(Request $request);
    static public function updateCustomId(Request $request);
    static public function showCustomId(Request $request);


    //Verification
    static public function showVerificationStatus(Request $request);
    static public function UploadVerificationFiles(Request $request);
    static public function initializeVerificationPayment(Request $request);
    static public function verifyUserVerificationPayment(Request $request);
    static public function updateGovernmentIssuedIdStatus(Request $request);
    static public function getPendingGovernmentIssuedID(Request $request);
    static public function IphoneUserVerificationPayment(Request $request);
    
}