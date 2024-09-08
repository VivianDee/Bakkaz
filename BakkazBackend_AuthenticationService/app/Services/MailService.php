<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use App\Mail\GeneralMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Http\Request;

class MailService
{
    public static function sendOtpMail(User $user, string $otp)
    {
        $mail = new OtpMail([
            "name" => $user->name == "" ? $user->first_name : $user->name,
            "otp" => $otp,
            "greeting" => "Hello",
            "intro" =>
            "Please use the following OTP to verify your email address.",
            "outro" => "This OTP is valid for 5 minutes.",
            "logoUrl" => asset(
                "https://res.cloudinary.com/dch8zvohv/image/upload/v1715941669/cloudinary-original/cp07qz3ydlhnaowkltg3.png",
                true
            ),
            "title" => "OTP Verification",
            "companyName" => "RecenthPost",

        ]);

        $status = Mail::mailer('dnr_smtp')->to($user->email)->send($mail);

        return $status ? true : false;
    }

    public static function sendWelcomeMail(User $user): bool
    {
        $mail = new WelcomeMail([
            "title" => 'We\'ve been waiting on you',
            "greeting" => "Welcome to RecenthPost",
            "name" => $user->first_name,
            "intro" =>
            "Thank you for joining our community! We are excited to have you with us.",
            "text" =>
            "Feel free to explore and let us know if you have any questions or need assistance.",
            "outro" => "We look forward to seeing you around!",
            "companyName" => "RecenthPost",
        ]);

        $status = Mail::mailer('dnr_smtp')->to($user->email)->send($mail);

        return $status ? true : false;
    }


    public static function sendAdminTag(User $user)
    {

        $mail = new GeneralMail([
            'title' => 'Admin Tag Mail',
            "name" => $user->first_name,
            'greeting' => 'Welcome to RecenthPost Admin, Please be productive',
            'body' => 'Feel free to explore and let us know if you have any questions or need assistance.\n Admin Tag: '.$user->admin_tag,
            'subject' => 'Admin Tag Retrival',
            "companyName" => "RecenthPost",
        ]);

        $status = Mail::mailer('support_smtp')->to($user->email)->send($mail);
        return ResponseHelpers::success($status);
    }

    public static function sendGeneralMail()
    {

        $mail = new GeneralMail([
            "title" => 'We\'ve been waiting on you',
            "greeting" => "Welcome to RecenthPost",
            "name" => 'Jesse Dan',
            "intro" =>
            "Thank you for joining our community! We are excited to have you with us.",
            "body" =>
            "Feel free to explore and let us know if you have any questions or need assistance.",
            "outro" => "We look forward to seeing you around!",
            "subject" => "RecenthPost",
        ]);

        $status = Mail::mailer('dnr_smtp')->to('jessedan160@gmail.com')->send($mail);

        return ResponseHelpers::success($status);
    }
}
