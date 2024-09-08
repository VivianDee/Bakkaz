<?php

namespace App\Enums;

enum AdminLogType: string
{
    case Delete = "Delete";
    case Create = "Create";
    case Validate = "Validate";
    case Verify = "Verify";
    case Update = "Update";
    case Other = "Other";
}