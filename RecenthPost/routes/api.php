<?php

use App\Http\Controllers\AdministrativeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HashtagContrioller;
use App\Http\Controllers\IntrestController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PollController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\StatController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ViewController;
use App\Models\Post;
use App\Http\Controllers\StreamController;



Route::middleware(["VerifyApiKey"])->group(function () {
    // Posts routes
    Route::delete("posts/{post_id}/{user_id}", [
        PostController::class,
        "deletePost",
    ]);
    Route::delete("comment/{comment_id}/{user_id}", [
        PostController::class,
        "deleteComment",
    ]);
    Route::delete("reply/{reply_id}/{user_id}", [
        PostController::class,
        "deleteReplies",
    ]);
    Route::post("posts", [PostController::class, "createPost"]);
    Route::get("posts", [PostController::class, "getAllPosts"]);

    // Poll routes
    Route::post("poll", [PollController::class, "createPost"]);
    Route::post("poll/vote", [PollController::class, "voteOnPoll"]);
    Route::get("poll/vote/{poll_id}", [PollController::class, "pollVotes"]);

    // Comments routes
    Route::post("comment", [CommentController::class, "commentOnPost"]);
    Route::get("comment/{post_id}", [CommentController::class, "getComments"]);

    // Replies routes
    Route::post("reply", [ReplyController::class, "replyToPost"]);
    Route::get("reply/{comment_id}", [ReplyController::class, "getReplies"]);

    // Views routes
    Route::post("views", [ViewController::class, "markAsViewed"]);
    Route::get("views/{post_id}", [ViewController::class, "getViews"]);

    // Reactions routes
    Route::post("reaction", [ReactionController::class, "reactOrUnreactPost"]);
    Route::get("reaction/{ref_id}/{ref_type}", [
        ReactionController::class,
        "getReactions",
    ]);

    // Shares routes
    Route::post('/share', [ShareController::class, 'sharePost']);
    Route::get('/shares/post/{post_id}', [ShareController::class, 'getSharesByPost']);
    Route::get('/shares/user/{user_id}', [ShareController::class, 'getSharesByUser']);


    Route::group(['prefix' => 'search'], function () {
        Route::get("/", [SearchController::class, "search"]);
        Route::get("sort/", [SearchController::class, "sortPost"]);
    });

    // Intrest routes
    Route::post("intrest", [IntrestController::class, "markPostAsUnintrested"]);

    // Favorite routes
    Route::post("favorite", [FavoriteController::class, "favorite"]);
    Route::get("favorite/{user_id}", [FavoriteController::class, "getFavorite"]);
    Route::get("mutual_favorite", [FavoriteController::class, "getMutualFavorites"]);
    Route::post("favorite/notification", [FavoriteController::class, "UpdateFavoritesPostNotification"]);


    // Hashtag routes
    Route::get("hashtag", [HashtagContrioller::class, "get"]);

    // Plans
    Route::get("plans", [PlanController::class, "index"]);
    Route::get("plans/{id}", [PlanController::class, "show"]);
    Route::post("plans", [PlanController::class, "store"]);
    Route::put("plans/{id}", [PlanController::class, "update"]);
    Route::delete("plans/{id}", [PlanController::class, "destroy"]);

    // Subscription
    Route::post("/subscribe", [SubscriptionController::class, "subscribe"]);
    Route::get("/verify-payment", [
        SubscriptionController::class,
        "verifyPayment",
    ]);
    Route::get("/unsubscribe/{user_id}", [SubscriptionController::class, "unsubscribe"]);
    Route::post("/change-plan", [SubscriptionController::class, "changePlan"]);
    Route::get("/subscription/current", [
        SubscriptionController::class,
        "getCurrentSubscriptionAndPlan",
    ]);

    Route::group(['prefix' => 'stat'], function () {
        Route::post('/', [StatController::class, 'createUserStats']);
        Route::get('/{user_id}', [StatController::class, 'getUserStat']);
    });

    Route::delete('/delete-interactions/{user_id}', [AdministrativeController::class, 'deleteInteractions']);

    Route::group(['prefix'=>'stream'],function () {
        // Route to get all streams with an optional filter
       Route::get('/', [StreamController::class, 'index']);

       // Route to start a new stream
       Route::post('/start', [StreamController::class, 'startStream']);

       // Route to stop an active stream
       Route::patch('/stop/{user_id}', [StreamController::class, 'stopStream']);

       // Route to get all streams by user ID with an optional filter
       Route::get('/user/{user_id}', [StreamController::class, 'getStreamsByUserId']);

       // Route to get an active stream by user ID
       Route::get('/{stream_id}', [StreamController::class, 'getStreamByStream']);

    });

});
Route::get('deep-link/post/{id}', function ($id) {
    $post = Post::find($id);

    if (!$post) {
        return response()->json(['message' => 'Post not found'], 404);
    }

    // Deep link URL
    $deepLinkUrl = 'recenthpost://post/' . $post->id;

    // Fallback URL
    $fallbackUrl = 'http://recenthpost.com';

    // Attempt to redirect to the deep link first
    return response("
        <html>
            <head>
                <script>
                    window.location.href = '{$deepLinkUrl}';
                    setTimeout(function() {
                        window.location.href = '{$fallbackUrl}';
                    }, 10000);  // 1 second delay before redirecting to fallback URL
                </script>
            </head>
            <body>
                <p>If you are not redirected automatically, <a href='{$fallbackUrl}'>click here</a>.</p>
            </body>
        </html>
    ", 200)->header('Content-Type', 'text/html');
});
