<?php

namespace App\Enums;

enum AccountType: string
{
    case GoogleSignUp = 'google-signup';
    case AnonymousSignUp = 'anonymous-signup';
}
