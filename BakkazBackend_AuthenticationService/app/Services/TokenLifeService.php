<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\TokenLife;
use App\Helpers\ResponseHelpers;

class TokenLifeService
{
    public static function setTokenExpTimes(Request $request)
    {
        $validatedData = $request->validate([
            "access_token_exp" => "required|integer|min:1",
            "refresh_token_exp" => "required|integer|min:1",
        ]);

        try {
            $tokenLife = TokenLife::first();
            if (!$tokenLife) {
                $tokenLife = new TokenLife();
            }

            $tokenLife->access_token_exp = $validatedData["access_token_exp"];
            $tokenLife->refresh_token_exp = $validatedData["refresh_token_exp"];
            $tokenLife->save();

            return ResponseHelpers::success(
                "Token expiration times set successfully."
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                "An error occurred while setting token expiration times: " .
                    $th->getMessage()
            );
        }
    }

    public static function getTokenExpTime()
    {
        try {
            $tokenLife = TokenLife::first();

            if (!$tokenLife) {
                return ResponseHelpers::notFound(
                    "Token expiration times not set."
                );
            }

            return ResponseHelpers::success([
                "access_token_exp" => $tokenLife->access_token_exp,
                "refresh_token_exp" => $tokenLife->refresh_token_exp,
            ]);
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                "An error occurred while retrieving token expiration times: " .
                    $th->getMessage()
            );
        }
    }
}
