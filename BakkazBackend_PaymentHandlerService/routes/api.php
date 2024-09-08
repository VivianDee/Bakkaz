<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(["VerifyApiKey"])->group(function () {
    // Inner routes for payment
    Route::group(["prefix" => "payment"], function () {
        // Transactions
        Route::post("/initialize_transaction", [
            PaymentController::class,
            "initializeTransaction",
        ]);
        Route::get("/verify", [
            PaymentController::class,
            "verifyTransaction",
        ])->where("reference", "[a-zA-Z0-9.-=]+");

        // Transfer
        Route::post("/initialize_transfer", [
            PaymentController::class,
            "initializeTransfer",
        ]);
        Route::post("/finalize_transfer", [
            PaymentController::class,
            "finalizeTransfer",
        ]);

        // Bulk Transfer
        Route::group(["prefix" => "bulk"], function () {
            Route::post("/initialize_transfer", [
                PaymentController::class,
                "initializeBulkTransfer",
            ]);
        });

        // Banks route
        Route::group(["prefix" => "banks"], function () {
            Route::get("/", [PaymentController::class, "showBanks"]);
        });

        // Transaction route
        Route::group(["prefix" => "transactions"], function () {
            Route::get("/", [PaymentController::class, "showTransactions"]);
            Route::get("/{id}", [
                PaymentController::class,
                "showTransactions",
            ])->where("id", "[0-9]+");
            Route::post("/charge", [
                PaymentController::class,
                "chargeTransaction",
            ]);
        });

        // Transfer route
        Route::group(["prefix" => "transfers"], function () {
            Route::get("/", [PaymentController::class, "showTransfers"]);
            Route::get("/{id}", [
                PaymentController::class,
                "showTransfers",
            ])->where("id", "[0-9]+");
        });

        // Transaction Timeline route
        Route::group(["prefix" => "transaction"], function () {
            Route::get("/timeline/{reference}", [
                PaymentController::class,
                "showTransactionTimeline",
            ])->where("reference", "[a-zA-Z0-9.-=]+");
        });

        // Refund routes
        Route::group(["prefix" => "refund"], function () {
            Route::post("/", [PaymentController::class, "initiateRefund"]);
        });

        // Refunds routes
        Route::group(["prefix" => "refunds"], function () {
            Route::get("/", [PaymentController::class, "showRefunds"]);
        });
    });
});

// Webhook route for handling external notifications from paystack
Route::middleware(["VerifyWebhookSignature"])->group(function () {
    Route::post("/webhook", [WebhookController::class, "handleWebhook"]);
});
