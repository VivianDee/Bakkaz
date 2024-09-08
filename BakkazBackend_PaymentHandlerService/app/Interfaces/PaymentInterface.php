<?php

namespace App\Interfaces;

use Illuminate\Http\Request;


interface PaymentInterface
{
    // Transactions
    static public function InitializeTransaction(Request $request);

    static public function verifyTransaction(Request $request);

    static public function showPaystackTransactions(Request $request);

    static public function showTransactions(Request $request);

    static public function showTransactionTimeline(Request $request);



    // Transfers
    static public function initializeTransfer(Request $request);

    static public function showBanks(Request $request);

    static public function initializeBulkTransfer(Request $request);
    




    // Refunds
    static public function initiateRefund(Request $request);

    static public function showRefunds(Request $request);
}
