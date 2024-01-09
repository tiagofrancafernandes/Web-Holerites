<?php

namespace App\Filament\Resources\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Filament\Facades\Filament;

trait ACLHelpers
{
    public static function allowed(
        array|string $toCheck,
        Model|null $record = null,
        ?User $user = null,
        bool $canAny = true,
        bool $toAddPrefix = true,
        ?string $prefix = null,
        array $extra = []
    ): bool {
        if (!$toCheck) {
            return false;
        }

        $user ??= Filament::auth()?->user();

        $arguments = array_merge([
            [
                $record,
            ],
            $extra,
        ]);

        $toCheck = collect(Arr::wrap($toCheck))
            ->filter(fn($item) => is_string($item) && filled($item));

        if ($toAddPrefix) {
            $modelClass = static::getModel();
            $prefix ??= str(class_basename($modelClass))->trim()->snake()->append('::')->toString();

            $toCheck = $toCheck
                ->map(
                    fn($item) => "$prefix{$item}"
                );
        }

        $checkResult = $canAny
            ? $user->canAny($toCheck?->toArray(), $arguments)
            : $user->can($toCheck?->toArray(), $arguments);

        // return $checkResult || dd($toCheck); // Show fail reason
        return $checkResult;
    }
}
