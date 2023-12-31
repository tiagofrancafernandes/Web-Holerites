<?php

namespace App\Enums;

use App\Enums\Traits\EnumLabel;

enum DocumentStatus: int
{
    use EnumLabel;

    case DRAFT = 10;
    case INVALID = 20;
    case VALIDATED = 30;
    case UNDER_ANALYSIS = 40;
    case REJECTED = 50;
    case APPROVED_FOR_PUBLICATION = 60;
    case AWAITING_REVIEW = 70;
    case PUBLISHED = 80;
}
