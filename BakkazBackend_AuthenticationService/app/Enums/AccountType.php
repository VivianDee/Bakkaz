<?php

namespace App\Enums;

enum AccountType: string
{
    case GoogleSignUp = 'google-signup';
    case AnonymousSignUp = 'anonymous-signup';
    case AdminSignUp = 'admin-signup';
    case SuperAdminSignUp = 'super-admin-signup';

}
