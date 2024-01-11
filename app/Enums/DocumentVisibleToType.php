<?php

namespace App\Enums;

use App\Enums\Traits\EnumLabel;

enum DocumentVisibleToType: int
{
    use EnumLabel;

    case EVERYONE = 10;
    case USER = 20;
    case GROUP = 30;

    public static function tryByValue(null|int|string|DocumentVisibleToType $value)
    {
        return match ($value) {
            DocumentVisibleToType::EVERYONE,
            DocumentVisibleToType::EVERYONE?->value,
            strval(DocumentVisibleToType::EVERYONE?->value),
            DocumentVisibleToType::EVERYONE?->name,
            DocumentVisibleToType::EVERYONE?->label(),
            strtolower(DocumentVisibleToType::EVERYONE?->name) => DocumentVisibleToType::EVERYONE,

            DocumentVisibleToType::USER,
            DocumentVisibleToType::USER?->value,
            strval(DocumentVisibleToType::USER?->value),
            DocumentVisibleToType::USER?->name,
            DocumentVisibleToType::USER?->label(),
            strtolower(DocumentVisibleToType::USER?->name) => DocumentVisibleToType::USER,

            DocumentVisibleToType::GROUP,
            DocumentVisibleToType::GROUP?->value,
            strval(DocumentVisibleToType::GROUP?->value),
            DocumentVisibleToType::GROUP?->name,
            DocumentVisibleToType::GROUP?->label(),
            strtolower(DocumentVisibleToType::GROUP?->name) => DocumentVisibleToType::GROUP,

            default => null,
        };
    }
}
