<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Concerns\Default\DefaultPageActions;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return DefaultPageActions::getPageHeaderActions(static::getResource());
    }

    protected function afterSave(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
