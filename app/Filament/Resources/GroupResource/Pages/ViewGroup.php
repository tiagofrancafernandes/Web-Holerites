<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGroup extends ViewRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label(GroupResource::getActionLabel('edit-mode'))
                ->color('gray'),
            Actions\CreateAction::make()
                ->label(GroupResource::getActionLabel('create')),
            Actions\DeleteAction::make()
                ->label(GroupResource::getActionLabel('delete')),
        ];
    }
}
