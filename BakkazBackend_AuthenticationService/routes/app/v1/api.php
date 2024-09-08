<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\FCMNotificationController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PushNotificationController;
use App\Services\TokenLifeService;

Route::middleware(["SetJsonResponse"])->group(function () {
    // Social Auth Routes
    Route::group(["prefix" => "google"], function () {
        Route::get("/", [AuthController::class, "redirectToGoogle"]);
        Route::get("/callback", [
            AuthController::class,
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
                Route::get("/refresh", [AuthController::class, "refresh"]);
            }
        );
        // Token Refresh Route for Authenticated Users with Specific Ability End

        // Auth Group Start
        Route::group(["prefix" => "auth"], function () {
            // Login Routes Start
            Route::group(["prefix" => "login"], function () {
                Route::post("/", [AuthController::class, "login"]);
            });
            // Login Routes End

            // Register Routes Start
            Route::group(["prefix" => "register"], function () {
                Route::post("/", [AuthController::class, "register"]);
                Route::post("/verify", [
                    AuthController::class,
                    "verifyAccount",
                ]);
            });
            // Register Routes End

            // Recover Account Routes Start
            Route::group(["prefix" => "recover-account"], function () {
                Route::get("/send-otp/{email}", [
                    AuthController::class,
                    "sendOtp",
                ]);
                Route::post("/verify-otp", [
                    AuthController::class,
                    "verifyOtp",
                ]);
                Route::post("/change-password", [
                    AuthController::class,
                    "changePassword",
                ]);
            });
            // Recover Account Routes End
        });
        // Auth Group End

        // Additional Recover Account Routes Start
        Route::group(["prefix" => "recover-account"], function () {
            Route::get("/send-otp/{email}", [
                AuthController::class,
                "sendOtp",
            ]);
            Route::post("/change-password", [
                AuthController::class,
                "changePassword",
            ]);
        });
        // Additional Recover Account Routes End

        // Logout Route for Authenticated Users Start
        Route::group(["middleware" => ["auth:sanctum"]], function () {
            Route::post("/logout", [AuthController::class, "logout"]);

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
        });
        // Logout Route for Authenticated Users End

        // Routes for Authenticated Users with API Access Ability Start
        Route::middleware([
            "auth:sanctum",
            "ability:" . TokenAbility::ACCESS_API->value,
        ])->group(function () {
            Route::post("/logout", [AuthController::class, "logout"]);

            // Auth Info Routes Start
            Route::group(["prefix" => "auth_info"], function () {
                Route::patch("/save-device", [
                    AuthController::class,
                    "saveDevice",
                ]);
                Route::patch("/update-prersent-location", [
                    AuthController::class,
                    "savePresentLocation",
                ]);
                Route::patch("/update-login-history", [
                    AuthController::class,
                    "saveLoginHistory",
                ]);
                Route::patch("/update-password-history", [
                    AuthController::class,
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
                    Route::get("/history/", [
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
            Route::get("/{id}", [UserController::class, "getUserById"]);
            Route::patch("/{id}", [
                UserController::class,
                "updateAuthInformation",
            ]);
            Route::delete("/{id}", [UserController::class, "deleteAccount"]);
        });
        // User Routes End
        //
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
});
