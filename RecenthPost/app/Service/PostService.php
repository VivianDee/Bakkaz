<?php

namespace App\Service;

use App\Helpers\ResponseHelpers;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Reply;
use App\Models\Intrest;
use App\Models\View;
use App\Models\Share;
use App\Impl\Services\AuthImpl;
use App\Impl\Services\PreferenceImpl;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class PostService
{
    public static function deleteReplies(Request $request)
    {
        try {
            $replies = Reply::where("id", $request->route("reply_id"))
                ->where("user_id", $request->route("user_id"))
                ->first();

            if ($replies) {
                $replies->delete();
                return ResponseHelpers::updated("Reply Deleted");
            } else {
                return ResponseHelpers::notFound();
            }
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError($th->getMessage());
        }
    }
    public static function deleteComment(Request $request)
    {
        try {
            $comment = Comment::where("id", $request->route("comment_id"))
                ->where("user_id", $request->route("user_id"))
                ->first();

            if ($comment) {
                Reply::where("comment_id", $comment->id)->delete();
                $comment->delete();
                return ResponseHelpers::updated("Comment Deleted");
            } else {
                return ResponseHelpers::notFound();
            }
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError($th->getMessage());
        }
    }
    public static function deletePost(Request $request)
    {
        try {
            $post = Post::where("id", $request->route("post_id"))
                ->where("user_id", $request->route("user_id"))
                ->first();



            if ($post) {
                $post->delete();
                if ($post->file) {
                    // AuthImpl::deletePostGroupedAsset();
                }
                return ResponseHelpers::success("Post Deleted");
            }

            return ResponseHelpers::notFound();
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError($th->getMessage());
        }
    }
    public static function createPost(Request $request)
    {
        ini_set("upload_max_filesize", "2000M");
        ini_set("post_max_size", "2000M");

        // Validate the request data
        $validated = Validator::make($request->all(), [
            "media_files" => "nullable|array",
            "media_files.*" => "nullable|max:209715200", // Limit file size
            "media_files_urls" => "sometimes|array",
            "media_files_urls.*" => "sometimes|url", // Ensure each URL is valid
            "title" => "nullable|string",
            "category_id" => "required|integer",
            "content" => "required|string",
            "state" => "required|string",
            "city" => "required|string",
            "device" => "required|string",
            "user_id" => "required",
            "countries_iso" => "required|array",
            "hashtags" => "sometimes|array",
        ]);

        // Check for validation errors
        if ($validated->fails()) {
            return ResponseHelpers::error(
                ResponseHelpers::implodeNestedArray($validated->errors(), [
                    "media_files",
                    "title",
                    "content",
                    "state",
                    "city",
                    "device",
                    "user_id",
                    "countries_iso",
                    "hashtags",
                ])
            );
        }

        try {
            DB::beginTransaction();

            // Check for duplicate post
            $existingPost = Post::where([
                ['user_id', $request->user_id],
                ['category_id', $request->category_id],
                ['title', $request->title],
                ['content', $request->content],
                ['state', $request->state],
                ['city', $request->city],
                ['device', $request->device],
            ])->exists();

            if ($existingPost) {
                return ResponseHelpers::unprocessableEntity('Duplicate post detected.');
            }

            // Handle file uploads (either URLs or physical files)
            $fileData = null;
            if ($request->has("media_files_urls")) {
                $fileData = AuthImpl::uploadAsset(
                    asset_type: "post-asset",
                    mediaFilesUrls: $request->media_files_urls,
                    uploadtype: "Urls"
                );

                // Validate file upload status for URLs
                if ($fileData && !$fileData["status"]) {
                    DB::rollBack();
                    return ResponseHelpers::error($fileData['message'] . "Unable to upload assets from URLs.");
                }
            } elseif ($request->has("media_files")) {
                $fileData = AuthImpl::uploadAsset(
                    asset_type: "post-asset",
                    files: $request->file("media_files"),
                    uploadtype: "Files"
                );

                // Validate file upload status for files
                if ($fileData && !$fileData["status"]) {
                    DB::rollBack();
                    return ResponseHelpers::error($fileData['message'] . "Unable to upload file assets.");
                }
            }

            // Create the post
            $post = Post::create([
                "user_id" => $request->user_id,
                "category_id" => $request->category_id,
                "title" => $request->title ?? '',
                "content" => $request->content,
                "state" => $request->state,
                "city" => $request->city,
                "device" => $request->device,
                "file" => $fileData["data"]["group_id"] ?? null,
            ]);

            if ($post) {
                // Associate countries with the post
                foreach ($request->countries_iso as $countryIso) {
                    Country::create([
                        "country_iso" => $countryIso,
                        "post_id" => $post->id,
                    ]);
                }

                // Assign hashtags to the post
                if (!empty($request->hashtags)) {
                    HashTagService::assignHashtagToPost($request->hashtags, $post->id);
                }

                // Commit the transaction
                DB::commit();

                $user_id = $request->user_id;
                $favorites = Favorite::where("ref_id", $user_id)
                    ->where("post_notification", true)
                    ->get()
                    ->pluck("user_id")
                    ->toArray();

                // Prepare notification data
                $dataFavouritePosts = [
                    "recipients" => $favorites,
                    "initiator_id" => $request->user_id,
                    "post_id" => $post->id,
                    "body" => $request->content,
                    "notification_type" => "favourites_posts",
                ];

                AuthImpl::sendActionNotification("favourites_posts", $dataFavouritePosts);

                StatService::incrementUserStat($request->user_id, 'posts_count');

               
                $post->load(['countries', 'hashtags']);
                $post->user_id = (int) $post->user_id;
                $post->user_deleted = (int) 0;
                $post->achieved = (int) 0;

                return ResponseHelpers::success(data: $post, message: "Resource created", statusCode: 201);
            }

            DB::rollBack();
            return ResponseHelpers::error("Failed to create post.");
        } catch (ValidationException $e) {
            DB::rollBack();
            return ResponseHelpers::error($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating post: ' . $e->getMessage());
            return ResponseHelpers::error("An unexpected error occurred.");
        }
    }

    public static function reactOrUnreactPost(Request $request)
    {
        $request->validate([
            "post_id" => "nullable|numeric",
            "comment_id" => "nullable|numeric",
            "reply_id" => "nullable|numeric",
            "type" => "required|numeric",
            "user_id" => "required|numeric",
        ]);

        // Ensure at least one of post_id, comment_id, or reply_id is provided
        if (
            !$request->post_id &&
            !$request->comment_id &&
            !$request->reply_id
        ) {
            return ResponseHelpers::error(
                "At least one of post_id, comment_id, or reply_id must be provided."
            );
        }

        try {
            $query = Reaction::where("user_id", $request->user_id);

            if ($request->post_id) {
                $query->where("post_id", $request->post_id);
            } elseif ($request->comment_id) {
                $query->where("comment_id", $request->comment_id);
            } elseif ($request->reply_id) {
                $query->where("reply_id", $request->reply_id);
            }

            $existingReaction = $query->first();

            if ($existingReaction) {
                if ($request->type > 5) {
                    return ResponseHelpers::error("Invalid Reaction. [1 - 5]");
                }
                if ($request->type <= 0) {
                    $existingReaction->delete();

                    if ($request->post_id) {
                        $post = Post::find($request->post_id);
                        if ($post) {
                            $recipientId = $post->user_id;
                            StatService::incrementUserStat($recipientId, "likes_count", true);
                            StatService::incrementUserStat($recipientId, "{$existingReaction->type}_star_count", true);
                        }
                    }

                    return ResponseHelpers::success("Unreacted");
                }
                $existingReaction->update(["type" => $request->type]);
                return ResponseHelpers::success("Reacted");
            }

            // Determine recipient_id based on the type of action
            $recipientId = null;

            if ($request->post_id) {
                $post = Post::find($request->post_id);
                if ($post) {
                    $recipientId = $post->user_id;
                    StatService::incrementUserStat($recipientId, "likes_count");
                    StatService::incrementUserStat($recipientId, "{$request->type}_star_count");
                }
            } elseif ($request->comment_id) {
                $comment = Comment::find($request->comment_id);
                if ($comment) {
                    $recipientId = $comment->user_id;
                }
            } elseif ($request->reply_id) {
                $reply = Reply::find($request->reply_id);
                if ($reply) {
                    $recipientId = $reply->user_id;
                }
            }

            // Prepare notification data
            $dataLikeOrComment = [
                "recipient_id" => $recipientId,
                "initiator_id" => $request->user_id,
                "post_id" => $request->post_id,
                "notification_type" => "like",
            ];

            AuthImpl::sendActionNotification("like", $dataLikeOrComment);

            // Create reaction
            Reaction::create([
                "user_id" => $request->user_id,
                "post_id" => $request->post_id,
                "comment_id" => $request->comment_id,
                "reply_id" => $request->reply_id,
                "type" => $request->type,
            ]);

            return ResponseHelpers::success("Reacted");
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                "An error occurred while reacting/unreacting the post: " .
                    $th->getMessage()
            );
        }
    }

    public static function commentOnPost(Request $request)
    {
        $request->validate([
            "content" => "required|string",
            "post_id" => "required|integer",
            "user_id" => "required|integer",
        ]);

        try {
            $comment = Comment::create(
                $request->only(["post_id", "user_id", "content"])
            );

            $recipient = $comment->post;

            $dataLikeOrComment = [
                "recipient_id" =>  $recipient->user_id,
                "initiator_id" => $request->get("user_id"),
                "post_id" => $request->post_id,
                "notification_type" => "comment",
            ];

            AuthImpl::sendActionNotification("comment", $dataLikeOrComment);

            if ($comment) {
                StatService::incrementUserStat($recipient->user_id, 'comments_count');
            }

            return $comment
                ? ResponseHelpers::created("Comment Sent")
                : ResponseHelpers::unprocessableEntity("Unable to comment");
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError($th->getMessage());
        }
    }

    public static function replyToPost(Request $request)
    {
        try {
            // Validate the request parameters
            $request->validate([
                "content" => "required|string",
                "comment_id" => "required|numeric",
                "user_id" => "required|integer",
            ]);

            // Find the comment based on comment_id
            $comment = Comment::find($request->get("comment_id"));
            if (!$comment) {
                return ResponseHelpers::unprocessableEntity(
                    "Comment not found"
                );
            }

            // Retrieve the post based on the comment's post_id
            $post = $comment->post;

            if (!$post) {
                return ResponseHelpers::unprocessableEntity("Post not found");
            }

            // Retrieve the post owner's user_id
            $commentOwnerId = $comment->user_id;
            if (!$commentOwnerId) {
                return ResponseHelpers::unprocessableEntity(
                    "Comment owner not found"
                );
            }

            // Prepare data for the notification
            $dataReply = [
                "recipient_id" => $commentOwnerId,
                "initiator_id" => $request->get("user_id"),
                "post_id" => $comment->post_id,
                "comment_id" => $request->get("comment_id"),
            ];

            // Send the notification
            AuthImpl::sendActionNotification("reply", $dataReply);

            // Create the reply
            $reply = Reply::create(
                $request->only(["comment_id", "user_id", "content"])
            );

            if ($reply) {
                StatService::incrementUserStat($commentOwnerId, 'replies_count');
            }

            return $reply
                ? ResponseHelpers::created("Reply Sent")
                : ResponseHelpers::unprocessableEntity("Failed to reply");
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError($th->getMessage());
        }
    }

    static public function sharePost(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'ref_id' => 'required|integer',
            'ref_type' => 'nullable|string',
        ]);

        try {
            // Fetch the user details
            $user = AuthImpl::getUserDetails($request->user_id);
            if (!$user) {
                return ResponseHelpers::unprocessableEntity("User not found");
            }

            // Initialize the URL variable
            $url = '';

            // Handle different reference types
            switch ($request->ref_type) {
                case 'post':
                    $post = Post::find($request->ref_id);
                    $postUser = $post->user_id;

                    if (!$post) {
                        return ResponseHelpers::notFound("Post not found");
                    } else {
                        // Generate a deep link URL
                        $url = url('api/deep-link/post/' . $post->id);
                    }
                    break;
                default:
                    return ResponseHelpers::unprocessableEntity("Invalid reference type");
            }

            // Create the Share
            $share = Share::create([
                "ref_id" => $request->ref_id,
                "ref_url" => $url,
                "user_id" => $request->user_id,
                "ref_type" => $request->ref_type ?? 'post',
            ]);

            if ($postUser) {
                StatService::incrementUserStat($postUser, 'shares_count');
            }

            return $share
                ? ResponseHelpers::success($share)
                : ResponseHelpers::unprocessableEntity("Failed to share post");
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError($th->getMessage());
        }
    }

    static public function getSharesByPost(Request $request)
    {
        $post_id = $request->route("post_id");

        $shares = Share::where('post_id', $post_id)->get();

        return $shares
            ? ResponseHelpers::success($shares->toArray())
            : ResponseHelpers::notFound();
    }

    static public function getSharesByUser(Request $request)
    {
        $user_id = $request->route("user_id");

        $shares = Share::where('user_id', $user_id)->get();

        return $shares
            ? ResponseHelpers::success($shares->toArray())
            : ResponseHelpers::notFound();
    }


    public static function addPostToFav(Request $request)
    {
        $request->validate([
            "ref_id" => "required|integer",
            "ref_name" => "required|string",
            "user_id" => "required|integer",
        ]);

        $user = $request->user_id;

        try {
            // Check if the favorite already exists
            $exists = Favorite::where("user_id", $user)
                ->where("ref_id", $request->ref_id)
                ->where("ref_name", $request->ref_name)
                ->exists();

            if ($exists) {
                Favorite::where("user_id", $user)
                    ->where("ref_id", $request->ref_id)
                    ->where("ref_name", $request->ref_name)
                    ->delete();


                return ResponseHelpers::success(
                    $request->ref_name . " removed from fav"
                );
            }

            // Create a new favorite
            Favorite::create([
                "user_id" => $user,
                "ref_id" => $request->ref_id,
                "ref_name" => $request->ref_name ?? "post",
            ]);

            // Determine the correct recipient_id based on ref_name
            $recipientId = null;

            if ($request->ref_name === "post") {
                $post = Post::find($request->ref_id);
                if ($post) {
                    $recipientId = $post->user_id;
                } else {
                    return ResponseHelpers::unprocessableEntity(
                        "Post not found"
                    );
                }
            } elseif ($request->ref_name === "user") {
                $user = AuthImpl::getUserDetails($request->ref_id);
                if ($user) {
                    $recipientId = $user["id"];
                } else {
                    return ResponseHelpers::unprocessableEntity(
                        "User not found"
                    );
                }
            }

            $isFavoritedByRefId = Favorite::where("user_id", $request->ref_id)
                ->where("ref_id", $request->user_id)
                ->where("ref_name", $request->ref_name)
                ->exists();

            // Prepare data for the notification
            $dataFavourite = [
                "recipient_id" => $recipientId,
                "initiator_id" => $request->user_id,
                "status" => $isFavoritedByRefId ? true : false
            ];

            AuthImpl::sendActionNotification("favourite", $dataFavourite);


            return ResponseHelpers::success(
                $request->ref_name . " added to fav"
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                "An error occurred while adding post to fav: " .
                    $th->getMessage()
            );
        }
    }

    public static function markPostAsUnintrested(Request $request)
    {
        $request->validate([
            "user_id" => "required|integer",
            "ref_id" => "required|numeric",
            "ref_name" => "required|string",
        ]);

        try {
            $existingRecord = Intrest::where("user_id", $request->user_id)
                ->where("ref_id", $request->ref_id)
                ->where("ref_name", $request->ref_name)
                ->first();

            if ($existingRecord) {
                $existingRecord->delete();
                return ResponseHelpers::success(
                    "{$request->ref_name} unmarked as interested"
                );
            }

            Intrest::create($request->only(["user_id", "ref_id", "ref_name"]));

            return ResponseHelpers::success(
                "{$request->ref_name} marked as interested"
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                "An error occurred while marking post as uninterested: " .
                    $th->getMessage()
            );
        }
    }

    public static function markAsView(Request $request)
    {
        $request->validate([
            "post_id" => "required|integer",
            "user_id" => "required|integer",
        ]);

        try {
            $exists = View::where([
                "user_id" => $request->user_id,
                "post_id" => $request->post_id,
            ])->exists();

            if ($exists) {
                return ResponseHelpers::unprocessableEntity(
                    "View already recorded"
                );
            }

            View::create($request->only(["user_id", "post_id"]));

            if ($request->post_id) {
                $post = Post::find($request->post_id);
                if ($post) {
                    $recipientId = $post->user_id;
                    StatService::incrementUserStat($recipientId, "views_count");
                }
            }

            return ResponseHelpers::created("View recorded successfully");
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError($th->getMessage());
        }
    }

    public static function getReactions(Request $request)
    {
        $ref_id = $request->route("ref_id");
        $ref_type = $request->route("ref_type");

        $column = match ($ref_type) {
            "post" => "post_id",
            "reply" => "reply_id",
            "comment" => "comment_id",
            default => null,
        };

        if (!$column) {
            return ResponseHelpers::notFound("type not found");
        }

        $relatedReactions = Reaction::where($column, $ref_id)->get();
        return ResponseHelpers::success($relatedReactions->toArray());
    }

    public static function getComments(Request $request)
    {
        $post_id = $request->route("post_id");
        $comments = Comment::where("post_id", $post_id)->get();

        // Add 'liked' attribute to each post if user_id is provided
        if ($userId = $request->query("user_id")) {
            foreach ($comments as $comment) {
                $comment->reacted = $comment->isLikedBy($userId);
            }
        }
        return $comments
            ? ResponseHelpers::success($comments->toArray())
            : ResponseHelpers::notFound();
    }

    public static function getReplies(Request $request)
    {
        $comment_id = $request->route("comment_id");
        $replies = Reply::where("comment_id", $comment_id)->get();

        // Add 'liked' attribute to each post if user_id is provided
        if ($userId = $request->query("user_id")) {
            foreach ($replies as $reply) {
                $reply->reacted = $reply->isLikedBy($userId);
            }
        }
        return $replies
            ? ResponseHelpers::success($replies->toArray())
            : ResponseHelpers::notFound();
    }

    public static function getViews(Request $request)
    {
        $post_id = $request->route("post_id");
        $views = View::where("post_id", $post_id)->get();

        return $views
            ? ResponseHelpers::success($views->toArray())
            : ResponseHelpers::notFound();
    }

    public static function getFavoriteUsers(Request $request)
    {
        $user_id = $request->route("user_id");
        $favorites = Favorite::where("user_id", $user_id)->get();

        $favorites_users = [];

        foreach ($favorites as $favorite) {
            $favorited_user_id = $favorite->ref_id;

            // Check if the favorited user has also favorited the current user
            $mutual_favorite = Favorite::where("user_id", $favorited_user_id)
                ->where("ref_id", $user_id)
                ->where("ref_name", "user")
                ->exists();

            if ($mutual_favorite) {
                $user = AuthImpl::getUserDetails($favorited_user_id, true);
                if ($user) {
                    $favorites_users[] = $user;
                }
            }
        }

        return $favorites_users
            ? ResponseHelpers::success($favorites_users)
            : ResponseHelpers::success(data: (object) []);
    }


    public static function getAllPosts(Request $request)
    {
        $perPage = $request->query("per_page", 15);
        $page = $request->query("page", 1);

        // Start building the query
        $query = Post::with(["hashtags", "countries"])->whereNull("deleted_at");

        // Exclude posts from blocked users
        if (!$request->has("favorite") && $user_id = $request->query("user_id")) {
            $blockedUsers = PreferenceImpl::getBlockedUsers($user_id);

            $ids = array_map(function ($blockedUsers) {
                return $blockedUsers['blocked_user_id'];
            }, $blockedUsers);

            $query->whereNotIn('user_id', $ids);
        }


        if ($postId = $request->query("post_id")) {
            return ResponseHelpers::success($query->find($postId));
        }

        // Apply filters based on query parameters
        if ($request->has("guest")) {
            $perPage = 5;
            $query->whereHas("countries", function ($q) {
                $q->where("country_iso", "NG");
            });
        } elseif ($countryIso = $request->query("country_iso")) {
            $query->whereHas("countries", function ($q) use ($countryIso) {
                $q->where("country_iso", $countryIso);
            });
        } elseif ($request->has("favorite")) {
            if (!($userId = $request->query("user_id"))) {
                return ResponseHelpers::unprocessableEntity(
                    "favorite requires a user id"
                );
            }

            // Retrieve all profile favorites for the user
            $favoriteProfileIds = Favorite::where("user_id", $userId)
                ->where("ref_name", "user")
                ->pluck("ref_id");

            // Filter posts where user_id (creator) is in the favoriteProfileIds
            $query->whereIn("user_id", $favoriteProfileIds);
        }

        if ($categoryId = $request->query("category_id")) {
            $query->where("category_id", $categoryId);
        } else {
            $query->where('category_id', '!=', '107');
        }

        // Sort the posts by creation date
        $query->orderBy("created_at", "desc");

        // Get paginated results
        $posts = $query->paginate($perPage, ["*"], "page", $page);

        // Add 'liked' attribute to each post if user_id is provided
        if ($userId = $request->query("user_id")) {
            foreach ($posts as $post) {
                $post->reacted = $post->isLikedBy($userId);
            }
        }

        // Remove unnecessary pagination links
        $res = collect($posts)->except([
            "prev_page_url",
            "first_page_url",
            "last_page_url",
            "links",
            "next_page_url",
            "path",
        ]);

        // Return the response
        return ResponseHelpers::success($res->toArray());
    }

    public static function getMutualFavorites(Request $request)
    {

        $isFavoritedByRefId = Favorite::where("user_id", $request->query('user_id'))
            ->where("ref_id", $request->query('ref_id'))
            ->where("ref_name", "user")
            ->exists();

        return ResponseHelpers::success(data: [
            "mutual_favourite" => $isFavoritedByRefId
        ]);
    }

    public static function UpdateFavoritesPostNotification(Request $request)
    {
        try {

            $request->validate([
                "user_id" => "required|integer",
                "ref_id" => "sometimes|integer",
                "post_notification" => "required|boolean",
            ]);


            $user_id = $request->input('user_id');
            $ref_id = $request->input('ref_id', null);
            $post_notification = $request->input('post_notification');

            if ($ref_id) {
                $favourite = Favorite::where("user_id", $user_id)
                    ->where("ref_id", $ref_id)
                    ->where("ref_name", "user")
                    ->first();

                if (!$favourite) {
                    $message = "Favorite not found.";
                }

                $favourite->update([
                    "post_notification" => $post_notification
                ]);

                $message = $post_notification ? "You are receiving post notifications from this user" : "You are not receiving post notifications from this user.";
            } else {
                $favourites = Favorite::where("user_id", $user_id)
                    ->where("ref_name", "user")
                    ->get();

                if ($favourites->isNotEmpty()) {
                    foreach ($favourites as $favourite) {
                        $favourite->update([
                            "post_notification" => $post_notification
                        ]);
                    }

                    $message = $post_notification
                        ? "You are now receiving post notifications from all users."
                        : "You have opted out of receiving post notifications from all users.";
                }
            }

            return ResponseHelpers::success(message: $message);
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    'ref_id',
                    'post_notification'
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }
}
