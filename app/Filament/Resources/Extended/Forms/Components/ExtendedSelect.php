<?php

namespace App\Filament\Resources\Extended\Forms\Components;

use Closure;

class ExtendedSelect extends \Filament\Forms\Components\Select
{
    protected bool | Closure $isNative = false;

    public function native(bool | Closure $condition = true): static
    {
        $this->isNative = $condition;

        return $this;
    }
}
