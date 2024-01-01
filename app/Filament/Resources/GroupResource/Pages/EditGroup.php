<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label(GroupResource::getActionLabel('view-mode')),
            Actions\CreateAction::make()
                ->label(GroupResource::getActionLabel('create')),
            Actions\DeleteAction::make()
                ->label(GroupResource::getActionLabel('delete')),
        ];
    }
}
