<?php

namespace App\Filament\Forms\Tables\Actions;

use Filament\Forms\Components\Select;
use Closure;

class AttachAction extends \Filament\Tables\Actions\AttachAction
{
    protected bool | Closure $canAttachMultiple = false;

    public function attachMultiple(bool | Closure $condition = true): static
    {
        $this->canAttachMultiple = $condition;

        return $this;
    }

    public function canAttachMultiple(): bool
    {
        return (bool) $this->evaluate($this->canAttachMultiple);
    }

    public function getRecordSelect(): Select
    {
        return parent::getRecordSelect()
            ->multiple($this->canAttachMultiple());
    }
}
