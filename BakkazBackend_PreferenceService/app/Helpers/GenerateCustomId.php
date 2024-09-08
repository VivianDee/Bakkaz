<?php 

namespace App\Helpers;

use App\Models\CustomId;

class GenerateCustomId
{
    static public function generateId()
    {
        $digits = 11;
        $min = pow(10, $digits - 1);
        $max = pow(10, $digits) - 1;

        do {
            $customId = "@RP" . (string) random_int($min, $max);
            $exists = CustomId::where('customized_username', $customId)->exists();
        } while ($exists);

        return $customId;
    }

}