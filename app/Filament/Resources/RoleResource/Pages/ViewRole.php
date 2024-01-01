<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Concerns\Default\DefaultPageActions;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return DefaultPageActions::getPageHeaderActions(static::getResource());
    }
}
