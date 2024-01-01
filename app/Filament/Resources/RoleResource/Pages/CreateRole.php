<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Concerns\Default\DefaultPageActions;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return DefaultPageActions::getPageHeaderActions(static::getResource());
    }

    protected function afterCreate(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
