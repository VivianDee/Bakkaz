<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\FCMTokenController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WebController;
use App\Services\TokenLifeService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveStreamController;




Route::middleware(["SetJsonResponse", "VerifyApiKey", "CurlMiddleware"])->group(function () {
    // Social Auth Routes
    Route::prefix("google")->group(function () {
        Route::get("/", [AuthController::class, "redirectToGoogle"]);
        Route::get("/callback", [AuthController::class, "handleGoogleCallback"]);
    });

    // Token Refresh Route for Authenticated Users with Specific Ability
    Route::middleware([
        "auth:sanctum",
        "ability:" . TokenAbility::ISSUE_ACCESS_TOKEN->value,
    ])->group(function () {
        Route::get("/refresh", [AuthController::class, "refresh"]);

    });

    // Auth Group
    Route::prefix("auth")->group(function () {
        // Login Routes
        Route::prefix("login")->group(function () {
            Route::post("/", [AuthController::class, "login"]);
        });

        // Register Routes
        Route::prefix("register")->group(function () {
            Route::post("/", [AuthController::class, "register"]);
            Route::post("/verify", [AuthController::class, "verifyAccount"]);
        });

        // Recover Account Routes
        Route::prefix("recover-account")->group(function () {
            Route::get("/send-otp/{email}", [AuthController::class, "sendOtp"]);
            Route::post("/verify-otp", [AuthController::class, "verifyOtp"]);
            Route::post("/change-password", [AuthController::class, "changePassword"]);
        });
    });

    // Routes for Authenticated Users with API Access Ability
    Route::middleware([
        "auth:sanctum",
        "ability:" . TokenAbility::ACCESS_API->value,
    ])->group(function () {
        Route::get("/email-verification-status", [AuthController::class, "getVerificationStatus"]);

        Route::post("/logout", [AuthController::class, "logout"]);

        // Chat Routes
        Route::prefix("chats")->group(function () {
            Route::post("/send-message", [ChatsController::class, "sendMessage"]);
            Route::get("/get-messages", [ChatsController::class, "getMessages"]);
            Route::post("/create-chat-room", [ChatsController::class, "createChatRoom"]);
            Route::get("/get-chat-rooms", [ChatsController::class, "getChatRooms"]);
            Route::post("/add-user-to-chat-room", [ChatsController::class, "addUserToChatRoom"]);
            Route::post("/remove-user-from-chat-room", [ChatsController::class, "removeUserFromChatRoom"]);
            Route::get("/get-chat-room-users", [ChatsController::class, "getChatRoomUsers"]);
            Route::post("/mark-message-as-read", [ChatsController::class, "markMessageAsRead"]);
            Route::get("/get-unread-messages-count/{user_id}", [ChatsController::class, "getUnreadMessagesCount"]);
            Route::post("/delete-message", [ChatsController::class, "deleteMessage"]);
            Route::post("/delete-chat-room", [ChatsController::class, "deleteChatRoom"]);
            Route::get("/get-chat-list", [ChatsController::class, "getChatList"]); // New route
        });

        // Auth Info Routes
        Route::prefix("auth_info")->group(function () {
            Route::patch("/save-device", [AuthController::class, "saveDevice"]);
            Route::patch("/update-present-location", [AuthController::class, "savePresentLocation"]);
            Route::patch("/update-login-history", [AuthController::class, "saveLoginHistory"]);
            Route::patch("/update-password-history", [AuthController::class, "savePasswordHistory"]);
        });

        // Media Routes
        Route::prefix("media")->group(function () {
            // Cover Asset Routes
            Route::prefix("cover")->group(function () {
                Route::post("/", [AssetsController::class, "saveCoverAsset"]);
                Route::get("/", [AssetsController::class, "getCoverAsset"]);
                Route::get("/history", [AssetsController::class, "getCoverAssetHistory"]);
                Route::delete("/", [AssetsController::class, "deleteLatestCoverAsset"]);
            });

            // Profile Asset Routes
            Route::prefix("profile")->group(function () {
                Route::post("/", [AssetsController::class, "saveProfileAsset"]);
                Route::get("/", [AssetsController::class, "getProfileAsset"]);
                Route::get("/{id}", [AssetsController::class, "getProfileAsset"]);
                Route::get("/history", [AssetsController::class, "getProfileAssetHistory"]);
                Route::delete("/", [AssetsController::class, "deleteLatestProfileAsset"]);
            });
        });

        // Admin Token Life Routes
        Route::prefix("token-life")->group(function () {
            Route::put("/set-token-exp-times", [TokenLifeService::class, "setTokenExpTimes"]);
            Route::get("/get-token-exp", [TokenLifeService::class, "getTokenExpTime"]);
        });

        Route::prefix("settings")->group(function () {
            // Change Password from Settings Routes
            Route::post("/change-password", [SettingsController::class, "changePassword"]);
        });

        // Settings Routes Start
        Route::group(["prefix" => "settings"], function () {

            // Change Password from Settings Routes Start
            Route::post("/change-password", [
                SettingsController::class,
                "changePassword",
            ]);
            // Change Password from Settings Routes End

        });
        // Settings Routes End

    });

    // Notification Routes
    Route::prefix("notification")->group(function () {
        Route::post("/mutual_favourite", [PushNotificationController::class, "updateMutualFavourites"]);
        Route::get("/", [PushNotificationController::class, "showNotifications"]);
        Route::get("/{user_id}", [PushNotificationController::class, "showNotifications"]);
        Route::post("/send", [PushNotificationController::class, "sendPushNotification"]);
        Route::put("/save-token", [FcmTokenController::class, "storeFcmToken"]);
        Route::delete("/delete-token", [FcmTokenController::class, "removeFcmToken"]);
        Route::patch("/read", [PushNotificationController::class, "MarkPushNotificationAsRead"]);
    });

    // User Routes
    Route::prefix("user")->group(function () {
        Route::get("/", [UserController::class, "getAllUsers"]);
        Route::get("/{id}", [UserController::class, "getUserById"]);
        Route::post("/", [UserController::class, "getUserByIds"]);
        Route::patch("/{id}", [UserController::class, "updateAuthInformation"]);
        Route::delete("/{id}", [UserController::class, "deleteAccount"]);
    });

    // Asset Routes
    Route::prefix("asset")->group(function () {
        // Grouped Asset Routes
        Route::post("/group", [AssetsController::class, "createGroupedAssets"]);
        Route::get("/group/{group_id}", [AssetsController::class, "getGroupedAssetsByGroupedAssetId"]);
        Route::post("/group/{group_id}", [AssetsController::class, "updateGroupedAssetsByGroupedAssetId"]);
        Route::delete("/group/{group_id}", [AssetsController::class, "deleteGroupedAssetsByGroupedAssetId"]);

        // Single Asset Routes
        Route::post("/group/single/{group_id}/{single_asset_id}", [AssetsController::class, "updateSingleAssetOnGroupedAssetBySingleAssetId"]);
        Route::get("/group/single/{group_id}/{single_asset_id}", [AssetsController::class, "getSingleAssetOnGroupedAssetBySingleAssetId"]);
        Route::delete("/group/single/{group_id}/{single_asset_id}", [AssetsController::class, "deleteSingleAssetOnGroupedAssetBySingleAssetId"]);

        Route::post("/get-downloadable-url", [AssetsController::class, "getDownloadableUrl"]);

    });

    // Resource Routes
    Route::prefix("resources")->group(function () {
        Route::get("/countries", [ResourceController::class, "getCountries"]);
        Route::get("/categories", [ResourceController::class, "getCategories"]);
        Route::get("/sub_categories", [ResourceController::class, "getSubCategories"]);
        Route::get("/sub_categories_child", [ResourceController::class, "getSubCategoriesChildren"]);
        Route::get("/get-server-date", [ResourceController::class, "getServerDate"]);

    });

    // Web Routes
    Route::prefix("web")->group(function () {
        Route::post("/contact-us", [WebController::class, "contactUs"]);
        Route::post("/subscribe", [WebController::class, "subscribe"]);
    });

    // Mail Routes
    Route::post("mail", [MailController::class, "sendGeneralMail"]);

    // Guest Routes
    Route::prefix("guest")->group(function () {
        Route::get("/profile_assets/{user_id}", [AssetsController::class, "getUserProfileAsset"]);
        Route::get("/cover_assets/{user_id}", [AssetsController::class, "getUserCoverAsset"]);
    });
});


Route::prefix('live-stream')->group(function () {
    Route::post('create', [LiveStreamController::class, 'createLiveStream']);
    Route::post('activate/{streamId}', [LiveStreamController::class, 'activateLiveStream']);
    Route::post('set-idle/{streamId}', [LiveStreamController::class, 'setLiveStreamToIdle']);
    Route::delete('delete/{streamId}', [LiveStreamController::class, 'deleteLiveStream']);
    Route::post('create-output/{streamId}', [LiveStreamController::class, 'createLiveStreamOutput']);
});
