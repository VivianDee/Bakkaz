<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;


class PaymentController extends Controller
{
    // Initialize Transaction
    public function InitializeTransaction(Request $request)
    {
        return PaymentService::InitializeTransaction($request);
    }


    // Verify a Transaction
    public function verifyTransaction(Request $request)
    {
        return PaymentService::verifyTransaction($request);
    }


    // Show Transactions
    public function showTransactions(Request $request)
    {
        return PaymentService::showTransactions($request);
    }

    // Show All Paystack Transactions
    public function showPaystackTransactions(Request $request)
    {
        return PaymentService::showPaystackTransactions($request);
    }


    // Show Transaction Timeline
    public function showTransactionTimeline(Request $request)
    {
        return PaymentService::showTransactionTimeline($request);
    }

    // Initiate Refund
    public function initiateRefund(Request $request)
    {
        return PaymentService::initiateRefund($request);
    }


    /// Show refunds
    public function showRefunds(Request $request)
    {
        return PaymentService::showRefunds($request);
    }


    /// Show Banks
    public function showBanks(Request $request)
    {
        return PaymentService::showBanks($request);
    }

    // Initialize Transfer
    public function initializeTransfer(Request $request)
    {
        return PaymentService::initializeTransfer($request);
    }

    public function finalizeTransfer(Request $request)
    {
        return PaymentService::finalizeTransfer($request);
    }

    public function initializeBulkTransfer(Request $request)
    {
        return PaymentService::initializeBulkTransfer($request);
    }

    // Show Transactions
    public function showTransfers(Request $request)
    {
        return PaymentService::showTransfers($request);
    }

}
