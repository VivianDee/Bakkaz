<?php

namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdministrativeService
{
    /**
     * Mark records as deleted in multiple tables.
     *
     * @param Request $request
     * @return void
     */
    static public function deleteInteractions(Request $request)
    {
        // Define the tables where user_deleted column needs to be updated
        $tables = [
            'posts',
            'comments',
            'reactions',
            'replies',
            'favorites',
            'user_stats',
            'views',
        ];

        // Get user ID from request
        $userId = $request->route('user_id');

        if ($userId) {
            foreach ($tables as $table) {
                // Mark records as deleted in each table
                DB::table($table)
                    ->where('user_id', $userId) // Assuming the table has a user_id column
                    ->update(['user_deleted' => true]);
            }
        }
    }
}
