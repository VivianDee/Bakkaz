<?php
namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Generate an OTP of a specified length and save it to the database.
     *
     * @param int $length
     * @param string $email
     * @return array
     */
    public static function generateOtp(int $length, string $email): array
    {
        // Check for existing active OTPs
        $activeOtp = Otp::where("email", $email)
            ->where("expires_at", ">", now())
            ->first();

        if ($activeOtp) {
            $activeOtp->delete();
            // return ["otp" => null, "message" => "An active OTP already exists"];
        }

        // Generate OTP
        $otp = self::generateUniqueOtp($length, $email, true);

        // Save OTP to the database with an expiration time (e.g., 10 minutes)
        Otp::create([
            "email" => $email,
            "otp" => $otp,
            "expires_at" => now()->addMinutes(5),
        ]);

        return ["otp" => $otp, "message" => "OTP sent successfully"];
    }

    /**
     * Generate a unique OTP.
     *
     * @param int $length
     * @param string $email
     * @param bool $deletePrevious
     * @return string
     */
    public static function generateUniqueOtp(
        int $length,
        string $email,
        bool $deletePrevious = false
    ): string {
        do {
            $otp = Str::random($length);
            $existingOtp = Otp::where("email", $email)
                ->where("otp", $otp)
                ->first();
        } while ($existingOtp !== null);

        // Check if there is an existing OTP to delete
        $previousOtp = Otp::where("email", $email)->first();
        if (
            $deletePrevious &&
            $previousOtp &&
            now()->isAfter($previousOtp->expires_at)
        ) {
            $previousOtp->delete();
        }

        return $otp;
    }
}
