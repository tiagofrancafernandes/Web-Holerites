<?php

namespace App\Filament\Helpers\Traits;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;

trait PermissionHelpers
{
    public static function can(string $action, ?Model $record = null): bool
    {
        $modelClass = class_basename($record ? get_class($record) : strval(static::getModel()));
        $permission = $modelClass ? str($modelClass)->trim()->snake()->append("::{$action}")->trim()->toString() : null;

        $permissionExists = cache()->remember(
            "permissionExists:{$permission}",
            60,
            fn () => Permission::whereName($permission)->exists()
        );

        if (!$permissionExists) {
            return false;
        }

        return parent::can($permission, $record);
    }
}
