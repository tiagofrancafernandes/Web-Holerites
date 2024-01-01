<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(static::getResource()::getActionLabel('create'))
                ->hidden(
                    fn(): bool => !auth()->user()->canAny([
                        'user::create',
                        'user::edit',
                    ])
                ),
        ];
    }
}
