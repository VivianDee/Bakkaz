<?php

namespace App\Http\Controllers;

use App\Services\MailService;
use Illuminate\Http\Request;

class MailController extends Controller
{
    public function sendGeneralMail()
    {
        return MailService::sendGeneralMail();
    }
}
