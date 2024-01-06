<?php

namespace App\Filament\Resources\Extended\Forms\Components;

use Closure;

class ExtendedDatePicker extends \Filament\Forms\Components\DatePicker
{
    protected bool | Closure $isNative = false;

    public function native(bool | Closure $condition = true): static
    {
        $this->isNative = $condition;

        return $this;
    }
}
