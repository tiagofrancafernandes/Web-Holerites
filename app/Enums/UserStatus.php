<?php

namespace App\Enums;

use App\Enums\Traits\EnumLabel;

enum UserStatus: int
{
    use EnumLabel;

    case INACTIVE = 0;
    case ACTIVE = 1;
}
