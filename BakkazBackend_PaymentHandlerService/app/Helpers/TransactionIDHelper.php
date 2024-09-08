<?php

namespace App\Helpers;

function generateTransacrionRefID(int $length = 16)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-.=';

    $charactersLength = strlen($characters);

    $transactionReference = '';

    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, $charactersLength - 1);
        $transactionReference .= $characters[$randomIndex];
    }

    return $transactionReference;
}