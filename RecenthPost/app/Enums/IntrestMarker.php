<?php

namespace App\Enums;

enum IntrestMarker: string
{
    case Post = "post";
    case Profile = "profile";
    case Poll = "poll";
    case Comment = "comment";
    case HashTag = "hashtag";
    case Reply = "reply";
    case Notification = "notification";
}
