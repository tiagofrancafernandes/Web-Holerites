<?php

namespace App\Filament\Resources\Extended;

use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

use function Filament\authorize;
use App\Filament\Resources\Traits\ModelLabel;
use App\Filament\Resources\Traits\ACLHelpers;

class ExtendedResourceBase extends \Filament\Resources\Resource
{
    use ModelLabel;
    use ACLHelpers;

    protected static ?bool $ignoreAcl = null;

    public static function can(string $action, ?Model $record = null): bool
    {
        if (!static::$ignoreAcl) {
            return static::allowed(
                $action,
                $record
            ); // get the reason
        }

        if (static::shouldSkipAuthorization()) {
            return true;
        }

        $model = static::getModel();

        try {
            return authorize($action, $record ?? $model, static::shouldCheckPolicyExistence())->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }
}
