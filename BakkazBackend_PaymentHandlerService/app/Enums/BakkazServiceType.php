<?php

namespace App\Enums;


enum BakkazServiceType: string
{
    case AuthService = 'auth-service';
    case PreferenceSeervice = 'preference-service';
    case ADsService = 'ads-service';
    case PaymentService = 'payment-service';
    case RecenthPosts = 'recenth-post-service';
    case Shopprar = 'shopprar-service';

}
