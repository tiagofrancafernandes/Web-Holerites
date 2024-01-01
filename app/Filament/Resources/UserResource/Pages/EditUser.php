<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(
                    function(Model $record): bool {
                        if (!auth()->user()->canAny([
                            // 'user::create',
                            'user::delete',
                            'user::deleteAny',
                            'user::edit',
                            'user::editAny',
                            'user::forceDelete',
                            'user::forceDeleteAny',
                            // 'user::list',
                            // 'user::listAll',
                            // 'user::reorder',
                            // 'user::reorderAny',
                            // 'user::restore',
                            // 'user::restoreAny',
                            'user::update',
                            'user::updateAny',
                        ])) {
                            return true;
                        }

                        return $record?->id === auth()->user()->id;
                    }
                )
                ->label(static::getResource()::getActionLabel('delete')),
        ];
    }
}
