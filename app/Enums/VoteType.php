<?php

namespace App\Enums;

enum VoteType: string
{
    case Helpful = 'helpful';
    case NotHelpful = 'not_helpful';
}
