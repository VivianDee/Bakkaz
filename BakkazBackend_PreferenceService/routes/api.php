<?php

use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\PaymentAmountController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\PreminumPostController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportPostsController;
use App\Http\Controllers\ReportProblemController;
use App\Http\Controllers\SecurityController;
use App\Models\NotificationSettings;
use Illuminate\Support\Facades\Route;

// Middlewear registered in bootstrap/app.php

//User preference 
Route::middleware(['VerifyApiKey'])->group(function () {

    // Inner group for preference routes
    Route::group(['prefix' => 'preferences'], function () {
        Route::get('/', [PreferenceController::class, 'showPreference']);
        Route::get('/stats', [PreferenceController::class, 'getStats']);
        Route::get('/{id}', [PreferenceController::class, 'showPreference']);
        Route::post('/create', [PreferenceController::class, 'createPreference']);
        Route::patch('/', [PreferenceController::class, 'updatePreference']);
        Route::delete('/{user_id}', [PreferenceController::class, 'deletePreference']);
        Route::patch('/restore/{user_id}', [PreferenceController::class, 'restorePreference']);


        // Inner group for Verification routes
        Route::group(['prefix' => 'user_verification'], function () {
            //Verification Payment     
            Route::post('/pay', [PreferenceController::class, 'initializeVerificationPayment']);
            Route::get('/verify', [PreferenceController::class, 'verifyUserVerificationPayment']);
            Route::post('/verify/iphone', [PreferenceController::class, 'IphoneUserVerificationPayment']);
            Route::get('/gid', [PreferenceController::class, 'getPendingGovernmentIssuedID']);
            Route::patch('/gid/update', [PreferenceController::class, 'updateGovernmentIssuedIdStatus']);
            Route::post('/', [PreferenceController::class, 'UploadVerificationFiles']);
            Route::get('/all', [PreferenceController::class, 'showVerificationStatus']);
            Route::get('/{user_id}', [PreferenceController::class, 'showVerificationStatus'])->where('id', '[0-9]+');
        });

        // Inner group for Custom ID routes
        Route::group(['prefix' => 'custom_id'], function () {

            Route::patch('/', [PreferenceController::class, 'updateCustomId']);
            Route::get('/search', [PreferenceController::class, 'searchCustomIds']);

            //Custom ID Payment
            Route::post('/pay', [PreferenceController::class, 'initializeCustomIdPayment']);
            Route::get('/verify', [PreferenceController::class, 'verifyCustomIdPayment']);

            Route::get('/{user_id}', [PreferenceController::class, 'showCustomId'])->where('id', '[0-9]+');
        });


        // Inner group for Premium Post routes
        Route::group(['prefix' => 'premium_post'], function () {

            Route::patch('/', [PreminumPostController::class, 'updatePremiumPost']);

            //Premium Post Payment
            Route::post('/pay', [PreminumPostController::class, 'initializePremiumPostPayment']);
            Route::get('/verify', [PreminumPostController::class, 'verifyPremiumPostPayment']);

            Route::get('/{user_id}', [PreminumPostController::class, 'showPremiumPost'])->where('id', '[0-9]+');
        });

        // Inner group for Subscription routes
        Route::group(['prefix' => 'subscription'], function () {
            Route::get('/status/{id}', [PreferenceController::class, 'Issuscribed']);
            Route::patch('/', [PreferenceController::class, 'updateSubscription']);
        });

        // Inner group for Language routes
        Route::group(['prefix' => 'language'], function () {
            Route::patch('/', [PreferenceController::class, 'updateLanguage']);
            Route::post('/', [PreferenceController::class, 'createLanguage']);
            Route::get('/', [PreferenceController::class, 'showLanguages']);
        });

        // Inner group for profile routes
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/{id}', [ProfileController::class, 'showProfile']);
            Route::patch('/', [ProfileController::class, 'updateUserProfile']);
        });

        // Inner group for profile routes
        Route::group(['prefix' => 'notification_settings'], function () {
            Route::get('/{id}', [NotificationSettingsController::class, 'showNotificationSettings']);
            Route::patch('/', [NotificationSettingsController::class, 'updateNotificationSettings']);
        });


        // Inner group for security routes
        Route::group(['prefix' => 'security'], function () {
            Route::get('/{id}', [SecurityController::class, 'showSecurity']);
            Route::patch('/update', [SecurityController::class, 'updateSecuritySettings']);
        });


        // Inner group for privacy routes
        Route::group(['prefix' => 'privacy'], function () {
            Route::get('/{user_id}', [PrivacyController::class, 'showPrivacy'])->where('user_id', '[0-9]+');
            Route::post('/show', [PrivacyController::class, 'showPrivacySettingsByIDs']);
            Route::patch('/', [PrivacyController::class, 'updatePrivacySettings']);
            Route::get('/non_mentionables', [PrivacyController::class, 'NonMentionables']);

            /// Block Users
            Route::get('/blocked_users/{id}', [PrivacyController::class, 'showBlockedUsers'])->where('id', '[0-9]+');
            Route::post('/block', [PrivacyController::class, 'blockUser']);

            /// Mute Users
            Route::get('/muted_users/{id}', [PrivacyController::class, 'showMutedUsers'])->where('id', '[0-9]+');
            Route::post('/mute', [PrivacyController::class, 'muteUser']);

            /// Report Users
            Route::get('/reported_users/{id}', [PrivacyController::class, 'showReportedUsers'])->where('id', '[0-9]+');;
            Route::post('/report_user', [PrivacyController::class, 'ReportUser']);


            /// Report Posts
            Route::get('/reported_posts/{id}', [ReportPostsController::class, 'showReportedPosts'])->where('id', '[0-9]+');;
            Route::post('/report_post', [ReportPostsController::class, 'ReportPost']);

            /// Report Problem
            Route::get('/reported_problem/{id}', [ReportProblemController::class, 'showReportedProblem'])->where('id', '[0-9]+');
            Route::post('/report_problem', [ReportProblemController::class, 'ReportProblem']);
        });
    });

    // Inner group for profile routes
    Route::group(['prefix' => 'payment_amount'], function () {
        Route::post('/', [PaymentAmountController::class, 'createPaymentAmount']);
        Route::get('/', [PaymentAmountController::class, 'showPaymentAmounts']);
        Route::patch('/', [PaymentAmountController::class, 'updatePaymentAmount']);
    });
});
