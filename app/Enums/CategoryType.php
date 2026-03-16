<?php

namespace App\Enums;

enum CategoryType: string
{
    case Post = 'post';
    case Business = 'business';
    case Both = 'both';
}
