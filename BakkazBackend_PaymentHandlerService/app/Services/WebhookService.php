<?php

namespace App\Services;

use Illuminate\Http\Request;

class WebhookService
{
    // Handle the webhook event
    public static function handle(Request $request)
    {

        $event = $request->input('event');
        $data = $request->input('data');
        
        switch ($event) {
            case 'paymentrequest.pending':
                self::handlePendingPayment($data);
                break;
            case 'paymentrequest.success':
                self::handleSuccessfulPayment($data);
                break;
            case 'charge.success':
                self::handleSuccessfulCharge($data);
                break;
            case 'refund.failed':
                break;
            case 'refund.pending':
                break;
            case 'refund.processed':
                break;
            case 'refund.processing':
                break;
            default:
        }
    }

    // Handle Pending Payment Event
    private static function handlePendingPayment(array $data)
    {
        return $data;
    }

    // Handle Successful Payment Event
    private static function handleSuccessfulPayment(array $data)
    {
        return $data;
    }

    // Handle Successful Transaction Event
    private static function handleSuccessfulCharge(array $data)
    {
    }
}
