<?php

namespace App\Filament\Resources\Extended\Forms\Components;

use Closure;

class ExtendedDateTimePicker extends \Filament\Forms\Components\DateTimePicker
{
    protected bool | Closure $isNative = false;

    public function native(bool | Closure $condition = true): static
    {
        $this->isNative = $condition;

        return $this;
    }
}
