<?php

namespace App\Filament\Resources\HoleriteResource\Pages;

use App\Filament\Resources\HoleriteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHolerite extends EditRecord
{
    protected static string $resource = HoleriteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
