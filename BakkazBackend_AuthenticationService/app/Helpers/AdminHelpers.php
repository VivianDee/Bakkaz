<?php

namespace App\Helpers;

use App\Models\GroupedAsset;
use App\Models\User;
use Illuminate\Support\Str;

class AdminHelpers{
    /**
     * Generate a unique admin tag.
     *
     * @return string
     */
   static  public function generateUniqueAdminTag()
    {
        do {
            // Generate a random admin tag
            $adminTag = 'BKZ' . date('Ymd') . sprintf('%04d', mt_rand(0, 9999));

            // Check if the generated tag exists in the users table
            $exists = User::where('admin_tag', $adminTag)->exists();
        } while ($exists);

        return $adminTag;
    }
}