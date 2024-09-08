<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ClickController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Middlewear registered in bootstrap/app.php

/**
 * Controller Methods:
 *
 * Default: index() - Retrieve a list of resources.
 * Display: show(Request $request) - Retrieve a specific resource by ID.
 * Create: create(Request $request) - Create a new resource.
 * Store: store(Request $request) - Store a newly created resource.
 * Update: update(Request $request, $id) - Update an existing resource.
 * Destroy: destroy(Request $request) - Delete an existing resource.
 */

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::middleware(['VerifyApiKey'])->group(function () {

    // Advertisement routes
    Route::group(['prefix' => 'advertisement'], function () {
        Route::get('/', [AdvertisementController::class, 'showAd']);
        Route::get('/{id}', [AdvertisementController::class, 'showAd'])->where('id', '[0-9]+');
        Route::post('/create', [AdvertisementController::class, 'createAd']);
        Route::patch('/update', [AdvertisementController::class, 'updateAd']);
        Route::patch('{ad_id}/restore', [AdvertisementController::class, 'restoreAd'])->where('id', '[0-9]+');
        Route::delete('{ad_id}/delete', [AdvertisementController::class, 'deleteAd'])->where('id', '[0-9]+');


        // Inner group to handle click routes
        Route::group(['prefix' => 'click'], function () {
            Route::post('/', [ClickController::class, 'handleClick']);
        });

        Route::get('/{ad_id}/reviews', [AdvertisementController::class, 'showReviews'])->where('id', '[0-9]+');

        // Inner group to handle review routes
        Route::group(['prefix' => 'review'], function () {
            Route::get('/', [ReviewController::class, 'showReviews']);
            Route::post('/create', [ReviewController::class, 'reviewAd']);
            Route::patch('/update', [ReviewController::class, 'updateReview']);
            Route::delete('{review_id}/delete', [ReviewController::class, 'deleteReview'])->where('id', '[0-9]+');
        });
    });
});
