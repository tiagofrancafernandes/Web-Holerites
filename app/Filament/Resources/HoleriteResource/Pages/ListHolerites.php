<?php

namespace App\Filament\Resources\HoleriteResource\Pages;

use Filament\Actions;
use App\Filament\Resources\HoleriteResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListHolerites extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = HoleriteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return HoleriteResource::getWidgets();
    }
}
