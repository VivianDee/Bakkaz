<?php

namespace App\Service;

use App\Helpers\ArrayHelper;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Impl\Services\PreferenceImpl;
use App\Models\Favorite;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostHashtag;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchService
{
    static public function search(Request $request)
    {
        try {
            $searchQuery = $request->query('search');
            $hash_tag = $request->query('hashtag');
            $is_tag_search = $request->query('is_tag_search', false);

            if (empty($searchQuery)) {
                return ResponseHelpers::error(message: "Search query cannot be empty");
            }

            $searchQueryLower = strtolower($searchQuery);
            $ids = [];
            $user_id = $request->query("user_id", 0);

            // Get blocked users
            if ($user_id) {
                $blockedUsers = PreferenceImpl::getBlockedUsers($user_id);

                $ids = array_map(function ($blockedUsers) {
                    return $blockedUsers['blocked_user_id'];
                }, $blockedUsers);
            }

            if ($hash_tag) {
                return self::searchByHashtag($searchQueryLower, $ids, $user_id);
            }

            return self::searchAll($searchQueryLower, $ids, $user_id, $is_tag_search);
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(message: "An unexpected error occured");
        }
    }

    private static function searchByHashtag(string $searchQueryLower, array $ids, int $user_id)
    {
        $hashtags = Hashtag::whereRaw('LOWER(hashtag) LIKE ?', ['%' . $searchQueryLower . '%'])->get();
        $addedPostIds = [];
        $hashtagPosts = collect();

        // $custom_ids = PreferenceImpl::searchCustomId($searchQueryLower);

        foreach ($hashtags as $hashtag) {
            $relatedPosts = Post::with(["hashtags", "countries"])->whereHas('postHashtags', function ($query) use ($hashtag) {
                $query->where('hashtag_id', $hashtag->id);
            })->whereNull('deleted_at')
                ->whereNotIn('user_id', $ids)
                ->get();

            $uniquePosts = $relatedPosts->filter(function ($post) use (&$addedPostIds) {
                if (!in_array($post->id, $addedPostIds)) {
                    $addedPostIds[] = $post->id;
                    return true;
                }
                return false;
            });

            $hashtagPosts = $hashtagPosts->merge($uniquePosts);
            $hashtag->post_count = $relatedPosts->count();
        }

        // Add 'liked' attribute to each post if user_id is provided
        if ($user_id) {
            // Add user info to each profile item
            $hashtagPosts->each(function ($post) use ($user_id) {
                $post->reacted = $post->isLikedBy($user_id);
            });
        }

        return ResponseHelpers::success(data: ["posts" => $hashtagPosts->toArray()]);
    }

    private static function searchAll(string $searchQueryLower, array $ids, int $user_id, bool $is_tag_search = false)
    {
        $users = AuthImpl::FilterUserAccounts(searchParam: $searchQueryLower);

        $custom_ids = PreferenceImpl::searchCustomId($searchQueryLower);

        if ($custom_ids) {

            $users_custom_id = AuthImpl::getUsersByIds($custom_ids->toArray());

            $mergedUsers = collect($users['data'])->merge($users_custom_id['data']);

            $uniqueUsers = $mergedUsers->unique('id')->values()->all();

            $users['data'] = $uniqueUsers;
        }

        // Filter out Blocked users users that can't be mentioned
        $users['data'] = array_values(array_filter($users['data'], function ($user) use ($ids, $is_tag_search) {
            if ($is_tag_search) {
                // If true, check both conditions: not blocked and is mentionable
                return !in_array($user['id'], $ids) && $user['visibility']['is_mentionable'];
            } else {
                // If false, only check that the user is not blocked
                return !in_array($user['id'], $ids);
            }
        }));




        $userIds = isset($users['data']) ? array_column($users['data'], 'id') : [];
        $userIds = array_diff($userIds, $ids);

        $posts = Post::with(["hashtags", "countries"])->where(function ($query) use ($searchQueryLower, $userIds, $ids) {
            $query->whereNull('deleted_at')
                ->whereNotIn('user_id', $ids)
                ->where(function ($query) use ($searchQueryLower, $userIds) {
                    $query->whereRaw('LOWER(title) LIKE ?', ['%' . $searchQueryLower . '%'])
                        ->orWhereRaw('LOWER(content) LIKE ?', ['%' . $searchQueryLower . '%'])
                        ->orWhereIn('user_id', $userIds);
                });
        })->get();

        $hashtags = Hashtag::whereRaw('LOWER(hashtag) LIKE ?', ['%' . $searchQueryLower . '%'])->get();

        foreach ($hashtags as $hashtag) {

            // Fetch posts associated with this hashtag
            $postCount = PostHashtag::where('hashtag_id', $hashtag->id)
                ->whereHas('post', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->count();

            $hashtag->post_count = $postCount;
        }

        if ($posts->isEmpty() && $hashtags->isEmpty() && !$users) {
            return ResponseHelpers::notFound(message: "No search results found");
        }

        // Add 'liked' attribute to each post if user_id is provided
        if ($user_id) {
            // Add user info to each profile item
            $posts->each(function ($post) use ($user_id) {
                $post->reacted = $post->isLikedBy($user_id);
            });
        }

        return ResponseHelpers::success(data: [
            "posts" => $posts->toArray(),
            "hashtags" => $hashtags->toArray(),
            "users" => $users['data'] ?? [],
        ]);
    }


    public static function sortPost(Request $request)
    {
        $perPage = $request->query("per_page", 15);
        $page = $request->query("page", 1);
        $sort = $request->query("sort");
        $userId = $request->query("user_id");

        // Initialize query
        $query = Post::with(["hashtags", "countries"])->where('deleted_at', null);

        // Handle sorting logic
        switch ($sort) {
            case "comment":
                $query->whereIn('id', Comment::where('user_id', $userId)->pluck('post_id'));
                break;
            case "fav-post":
                $query->whereIn('id', Favorite::where('user_id', $userId)->pluck('ref_id'));
                break;
            case "fav-user":
                // Retrieve favorite user IDs
                $favUserIds = Favorite::where('user_id', $userId)->pluck('ref_id')->toArray();

                // Check if there are favorite user IDs
                if (empty($favUserIds)) {
                    return ResponseHelpers::error("No favorite users found for this user.");
                }

                // Collect all favorite users and handle pagination
                $favoriteUsers = [];
                foreach ($favUserIds as $favUserId) {
                    $userDetails = AuthImpl::FilterUserAccountsForSortPost($favUserId, $perPage, $page);
                    if ($userDetails) {
                        $favoriteUsers[] = $userDetails['users'];
                    }
                }

                // Flatten the array of users
                $favoriteUsers = array_merge(...$favoriteUsers);

                // Prepare the pagination data
                $pagination = [
                    'current_page' => $userDetails['pagination']['current_page'] ?? 1,
                    'per_page' => $userDetails['pagination']['per_page'] ?? $perPage,
                    'total' => $userDetails['pagination']['total'] ?? count($favoriteUsers),
                    'last_page' => $userDetails['pagination']['last_page'] ?? 1,
                    'first_page_url' => $userDetails['pagination']['first_page_url'] ?? url("user?per_page=$perPage&page=1"),
                    'last_page_url' => $userDetails['pagination']['last_page_url'] ?? url("user?per_page=$perPage&page={$userDetails['pagination']['last_page']}"),
                    'next_page_url' => $userDetails['pagination']['next_page_url'],
                    'prev_page_url' => $userDetails['pagination']['prev_page_url'],
                    'path' => $userDetails['pagination']['path'] ?? url("user"),
                ];

                // Return paginated favorite users response
                return ResponseHelpers::success([
                    'data' => $favoriteUsers,
                    'pagination' => $pagination,
                ]);
                break;

            default:
                if ($userId) {
                    $query->where('user_id', $userId);
                }
                break;
        }

        // Default sorting by creation date
        $query->orderBy("created_at", "desc");

        // Paginate results
        $posts = $query->paginate($perPage, ["*"], "page", $page);

        // Add reacted attribute if user_id is provided
        if ($userId) {
            $posts->getCollection()->transform(function ($post) use ($userId) {
                $post->reacted = $post->isLikedBy($userId);
                return $post;
            });
        }

        // Prepare response with pagination data
        $res = [
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
                'first_page_url' => $posts->url(1),
                'last_page_url' => $posts->url($posts->lastPage()),
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
                'path' => url($request->path()),
            ],
        ];

        return ResponseHelpers::success($res);
    }
}
