<?php

namespace App\Interfaces;

use Illuminate\Http\Request;


interface WebhookInterface
{
    // Handle Paystack Events
    static public function handleWebhook(Request $request);
}
