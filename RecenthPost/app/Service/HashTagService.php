<?php

namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHelpers;
use App\Models\Hashtag;
use App\Models\PostHashtag;

class HashTagService
{
    public static function assignHashtagToPost(array $hashtags, $post_id)
    {
        try {
            foreach ($hashtags as $hashtagName) {

            if( trim($hashtagName) != '' ){
                // Check if the hashtag exists
                $hashtag = Hashtag::where("hashtag", $hashtagName)->first();

                if (!$hashtag) {
                    // Create the hashtag if it doesn't exist
                    $hashtag = Hashtag::create(["hashtag" => $hashtagName]);
                }

                // Check if the post already has this hashtag assigned
                $exists = PostHashtag::where("post_id", $post_id)
                    ->where("hashtag_id", $hashtag->id)
                    ->exists();

                if (!$exists) {
                    // If not assigned, create the association
                    PostHashtag::create([
                        "post_id" => $post_id,
                        "hashtag_id" => $hashtag->id,
                        "created_at" => now(),
                        "updated_at" => now(),
                    ]);
                }
            }
            }

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function getAllHashTag(Request $request)
    {
        $id = $request->query("id");
        $hashtag = $request->query("hashtag");

        // Validate query parameters
        $request->validate([
            "id" => "nullable|integer",
            "hashtag" => "nullable|string|max:255",
        ]);

        try {
            $query = Hashtag::with("posthashTags");

            // Filter by id if provided
            if ($id) {
                $query->where("id", $id);
            }

            // Filter by hashtag if provided
            if ($hashtag) {
                $query->where("hashtag", $hashtag);
            }

            // Execute the query and get the results
            $results = $query->get();

            // Check if any results were found
            if ($results->isEmpty()) {
                return ResponseHelpers::notFound(
                    "No hashtags found matching the criteria"
                );
            }

            // Return the results
            return ResponseHelpers::success($results);
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                "An error occurred while retrieving hashtags: " .
                    $th->getMessage()
            );
        }
    }
}
