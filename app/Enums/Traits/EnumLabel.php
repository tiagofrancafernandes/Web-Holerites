<?php

namespace App\Enums\Traits;

trait EnumLabel
{
    public function label(): ?string
    {
        return __(
            str(__CLASS__)
                ->afterLast('\\')
                ->kebab()
                ->prepend('enums/')
                ->append('.')
                ->append($this->name)
                ->toString()
        );
    }
}
