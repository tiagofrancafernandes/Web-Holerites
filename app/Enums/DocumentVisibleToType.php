<?php

namespace App\Enums;

use App\Enums\Traits\EnumLabel;

enum DocumentVisibleToType: int
{
    use EnumLabel;

    case EVERYONE = 10;
    case USER = 20;
    case GROUP = 30;
}
