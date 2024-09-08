<?php

namespace App\Service;

use App\Models\UserStat;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatService
{

    /**
     * Create user stats with all values set to 0.
     *
     * @param int $userId
     * @return UserStat
     */
    static public function createUserStats($userId)
    {
        // Check if user stats already exist
        $userStat = UserStat::where('user_id', $userId)->first();

        if (!$userStat) {
            // Create user stats with all values set to 0
            $userStat = UserStat::create([
                'user_id' => $userId,
                'posts_count' => 0,
                'comments_count' => 0,
                'replies_count' => 0,
                'likes_count' => 0,
                'shares_count' => 0,
                'views_count' => 0,
                '1_star_count' => 0,
                '2_star_count' => 0,
                '3_star_count' => 0,
                '4_star_count' => 0,
                '5_star_count' => 0,
            ]);
        }

        return $userStat;
    }

    /**
     * Update or create user statistics.
     *
     * @param int $userId
     * @return UserStat
     */
    static public function getUserStats($userId)
    {
        $stats = UserStat::where('user_id', $userId)->first();

        if (!$stats) {
            $stats = Self::createUserStats($userId);
        }
        
        return $stats;
    }

    /**
     * Increment a specific field in the user's statistics by 1.
     *
     * @param int $userId
     * @param string $field
     * @return UserStat|null
     */
    static public function incrementUserStat($userId, $field, $decrease=false)
    {
        // Check if the field exists in the UserStat model
        if (!Schema::hasColumn('user_stats', $field)) {
            return null; // or throw an exception if you want to handle it that way
        }

        // Retrieve existing stats
        $userStat = UserStat::where('user_id', $userId)->first();

        if ($decrease && $userStat) {
            // Decrement the field by 1
            $userStat->decrement($field);

            
            return $userStat;
        }



        if ($userStat) {
            // Increment the field by 1
            $userStat->increment($field);
        } else {
            // If no stats exist for the user, create one with the field set to 1
            $userStat = UserStat::create([
                'user_id' => $userId,
                'posts_count' => 0,
                'comments_count' => 0,
                'replies_count' => 0,
                'likes_count' => 0,
                'shares_count' => 0,
                'views_count' => 0,
                '1_star_count' => 0,
                '2_star_count' => 0,
                '3_star_count' => 0,
                '4_star_count' => 0,
                '5_star_count' => 0,
            ]);


            $userStat->increment($field);
        }

        return $userStat;
    }

    
}
