<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\FCMNotificationController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PushNotificationController;
use App\Services\TokenLifeService;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\AdminPreferenceController;



Route::get('lol', function () {
    return 'zsh';
});
// Social Auth Routes
Route::group(["prefix" => "google"], function () {
    Route::get("/", [AdminAuthController::class, "redirectToGoogle"]);
    Route::get("/callback", [
        AdminAuthController::class,
        "handleGoogleCallback",
    ]);
});

// Auth Routes
Route::middleware(["VerifyApiKey", "CurlMiddleware"])->group(function () {
    // Token Refresh Route for Authenticated Users with Specific Ability Start
    Route::group(
        [
            "middleware" => [
                "auth:sanctum",
                "ability:" . TokenAbility::ISSUE_ACCESS_TOKEN->value,
            ],
        ],
        function () {
            Route::get("/refresh", [AdminAuthController::class, "refresh"]);
        }
    );
    // Token Refresh Route for Authenticated Users with Specific Ability End

    // Auth Group Start
    Route::group(["prefix" => "auth"], function () {
        // Login Routes Start
        Route::group(["prefix" => "login"], function () {
            Route::post("/", [AdminAuthController::class, "login"]);
        });
        // Login Routes End

        // Register Routes Start
        Route::group(["prefix" => "register"], function () {
            Route::post("/", [AdminAuthController::class, "register"]);
            Route::post("/verify", [
                AdminAuthController::class,
                "verifyAccount",
            ]);
        });
        // Register Routes End

        // Recover Account Routes Start
        Route::group(["prefix" => "recover-account"], function () {
            Route::get("/send-otp/{email}", [
                AdminAuthController::class,
                "sendOtp",
            ]);
            Route::post("/verify-otp", [
                AdminAuthController::class,
                "verifyOtp",
            ]);
            Route::post("/change-password", [
                AdminAuthController::class,
                "changePassword",
            ]);
        });
        // Recover Account Routes End
    });
    // Auth Group End

    // Additional Recover Account Routes Start
    Route::group(["prefix" => "recover-account"], function () {
        Route::get("/send-otp/{email}", [
            AdminAuthController::class,
            "sendOtp",
        ]);
        Route::post("/change-password", [
            AdminAuthController::class,
            "changePassword",
        ]);
    });
    // Additional Recover Account Routes End

    // Logout Route for Authenticated Users Start
    Route::group(["middleware" => ["auth:sanctum"]], function () {
        Route::post("/logout", [AdminAuthController::class, "logout"]);

        // Chat Routes Start
        Route::prefix("chats")->group(function () {
            Route::post("/send-message", [
                ChatsController::class,
                "sendMessage",
            ]);
            Route::get("/get-messages", [
                ChatsController::class,
                "getMessages",
            ]);
            Route::post("/create-chat-room", [
                ChatsController::class,
                "createChatRoom",
            ]);
            Route::get("/get-chat-rooms", [
                ChatsController::class,
                "getChatRooms",
            ]);
            Route::post("/add-user-to-chat-room", [
                ChatsController::class,
                "addUserToChatRoom",
            ]);
            Route::post("/remove-user-from-chat-room", [
                ChatsController::class,
                "removeUserFromChatRoom",
            ]);
            Route::get("/get-chat-room-users", [
                ChatsController::class,
                "getChatRoomUsers",
            ]);
            Route::post("/mark-message-as-read", [
                ChatsController::class,
                "markMessageAsRead",
            ]);
            Route::get("/get-unread-messages-count", [
                ChatsController::class,
                "getUnreadMessagesCount",
            ]);
            Route::post("/delete-message", [
                ChatsController::class,
                "deleteMessage",
            ]);
            Route::post("/delete-chat-room", [
                ChatsController::class,
                "deleteChatRoom",
            ]);
            Route::get("/get-chat-list", [
                ChatsController::class,
                "getChatList",
            ]); // New route
        });

        //Chat routes End



        /// Admin Preferences
        Route::prefix('admin-preferences')->group(function () {
            // Assign a preference to an admin
            Route::post('/assign', [AdminPreferenceController::class, 'assignPreferenceToAdmin'])
                ->name('admin.preferences.assign');
        
            // Remove a preference from an admin
            Route::delete('/remove/{admin_id}', [AdminPreferenceController::class, 'removePreferenceFromAdmin'])
                ->name('admin.preferences.remove');
        
            // Get all preferences for a specific admin
            Route::get('/{admin_id}', [AdminPreferenceController::class, 'getAdminPreferences'])
                ->name('admin.preferences.get');
        
            // Get all preferences
            Route::get('/', [AdminPreferenceController::class, 'getAllPreferences'])
                ->name('admin.preferences.all');
        });

        /// Platforms
        // Group routes for platform management
        Route::prefix('platform')->group(function () {
            // Add a platform to admin platforms
            Route::post('admin/{admin_id}/platform/{platform_id}', [PlatformController::class, 'addPlatformToAdminPlatforms'])
                ->name('platforms.addToAdmin');

            // Remove a platform from admin platforms
            Route::delete('admin/{admin_id}/platform/{platform_id}', [PlatformController::class, 'removePlatformFromAdminPlatforms'])
                ->name('platforms.removeFromAdmin');

            // Create a new platform
            Route::post('', [PlatformController::class, 'createPlatform'])
                ->name('platforms.create');

            // Delete an existing platform
            Route::delete('{platform_id}', [PlatformController::class, 'deletePlatform'])
                ->name('platforms.delete');

            // Update a platform
            Route::put('{platform_id}', [PlatformController::class, 'updatePlatform'])
                ->name('platforms.update');

            // Get all platforms for an admin
            Route::get('admin/{admin_id}', [PlatformController::class, 'getAdminPlatform'])
                ->name('platforms.getAdminPlatforms');

            // Get all platforms
            Route::get('', [PlatformController::class, 'getPlatform'])
                ->name('platforms.getAll');
        });
    });
    // Logout Route for Authenticated Users End

    // Routes for Authenticated Users with API Access Ability Start
    Route::middleware([
        "auth:sanctum",
        "ability:" . TokenAbility::ADMIN_ACCESS_API->value,
    ])->group(function () {
        Route::post("/logout", [AdminAuthController::class, "logout"]);

        // Auth Info Routes Start
        Route::group(["prefix" => "auth_info"], function () {
            Route::patch("/save-device", [
                AdminAuthController::class,
                "saveDevice",
            ]);
            Route::patch("/update-prersent-location", [
                AdminAuthController::class,
                "savePresentLocation",
            ]);
            Route::patch("/update-login-history", [
                AdminAuthController::class,
                "saveLoginHistory",
            ]);
            Route::patch("/update-password-history", [
                AdminAuthController::class,
                "savePasswordHistory",
            ]);
        });
        // Auth Info Routes End

        // Media Routes Start
        Route::group(["prefix" => "media"], function () {
            // Cover Asset Routes Start
            Route::group(["prefix" => "cover"], function () {
                Route::post("/", [
                    AssetsController::class,
                    "saveCoverAsset",
                ]);
                Route::get("/", [AssetsController::class, "getCoverAsset"]);
                Route::get("/history", [
                    AssetsController::class,
                    "getCoverAssetHistory",
                ]);
                Route::delete("/", [
                    AssetsController::class,
                    "deleteLatestCoverAsset",
                ]);
            });
            // Cover Asset Routes End

            // Profile Asset Routes Start
            Route::group(["prefix" => "profile"], function () {
                Route::post("/", [
                    AssetsController::class,
                    "saveProfileAsset",
                ]);
                Route::get("/", [
                    AssetsController::class,
                    "getProfileAsset",
                ]);
                Route::get("/{id}", [
                    AssetsController::class,
                    "getProfileAsset",
                ]);
                Route::get("/history", [
                    AssetsController::class,
                    "getProfileAssetHistory",
                ]);
                Route::delete("/", [
                    AssetsController::class,
                    "deleteLatestProfileAsset",
                ]);
            });
            // Profile Asset Routes End
        });
        // Media Routes End

        // Admin Token Life Routes Start
        Route::group(["prefix" => "token-life"], function () {
            Route::put("/set-token-exp-times", [
                TokenLifeService::class,
                "setTokenExpTimes",
            ]);
            Route::get("/get-token-exp", [
                TokenLifeService::class,
                "getTokenExpTime",
            ]);
        });
        // Admin Token Life Routes End
    });
    // Routes for Authenticated Users with API Access Ability End
    // Notification Routes Start
    Route::group(["prefix" => "notification"], function () {
        Route::get("/", [
            PushNotificationController::class,
            "showNotifications",
        ]);
        Route::get("/{user_id}", [
            PushNotificationController::class,
            "showNotifications",
        ]);
        Route::post("/send", [
            PushNotificationController::class,
            "sendPushNotification",
        ]);
        Route::put("/save-token", [
            PushNotificationController::class,
            "updateToken",
        ]);
        Route::patch("/read/{notification_id}", [
            PushNotificationController::class,
            "MarkPushNotificationAsRead",
        ]);
    });
    // Notification Routes End
        // User Routes Start
        Route::group(["prefix" => "user"], function () {
            Route::get("/", [UserController::class, "getAllUsers"]); 
            Route::get("/stats", [UserController::class, "getUsersStats"]);     
            Route::post("/suspend/{id}", [UserController::class, "suspendUser"]);
            Route::get("/{id}", [UserController::class, "getUserById"]);
            Route::patch("/{id}", [
                UserController::class,
                "updateAuthInformation",
        ]);
        Route::delete("/{id}", [UserController::class, "deleteAccount"]);
    });
    // User Routes End
    
    // Asset Routes Start
    Route::group(["prefix" => "asset"], function () {
        // Grouped Asset Routes Start
        Route::post("/group", [
            AssetsController::class,
            "createGroupedAssets",
        ]);
        Route::get("/group/{group_id}", [
            AssetsController::class,
            "getGroupedAssetsByGroupedAssetId",
        ]);
        Route::post("/group/{group_id}", [
            AssetsController::class,
            "updateGroupedAssetsByGroupedAssetId",
        ]);
        Route::delete("/group/{group_id}", [
            AssetsController::class,
            "deleteGroupedAssetsByGroupedAssetId",
        ]);
        // Grouped Asset Routes End

        // Single Asset Routes Start
        Route::post("/group/single/{group_id}/{single_asset_id}", [
            AssetsController::class,
            "updateSingleAssetOnGroupedAssetBySingleAssetId",
        ]);
        Route::get("/group/single/{group_id}/{single_asset_id}", [
            AssetsController::class,
            "getSingleAssetOnGroupedAssetBySingleAssetId",
        ]);
        Route::delete("/group/single/{group_id}/{single_asset_id}", [
            AssetsController::class,
            "deleteSingleAssetOnGroupedAssetBySingleAssetId",
        ]);
        // Single Asset Routes End
    });
    // Asset Routes End

    // Resource Routes Start
    Route::group(["prefix" => "resources"], function () {
        Route::get("/countries", [
            ResourceController::class,
            "getCountries",
        ]);
        Route::get("/categories", [
            ResourceController::class,
            "getCategories",
        ]);
        Route::get("/sub_categories", [
            ResourceController::class,
            "getSubCategories",
        ]);
        Route::get("/sub_categories_child", [
            ResourceController::class,
            "getSubCategoriesChildren",
        ]);
    });
    // Resource Routes End
});

Route::post("mail", [
    MailController::class,
    "sendGeneralMail",
]);

// Guest Routes Start
Route::group(["prefix" => "guest"], function () {
    Route::get('/profile_assets/{user_id}', [AssetsController::class, 'getUserProfileAsset']);
    Route::get('/cover_assets/{user_id}', [AssetsController::class, 'getUserCoverAsset']);
});
    // Guest Routes End