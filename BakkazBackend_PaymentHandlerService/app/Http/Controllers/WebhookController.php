<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        return WebhookService::handle($request);

    }
}
