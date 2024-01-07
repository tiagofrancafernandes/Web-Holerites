<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;


    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $data['password'] = $data['password'] ?? \Hash::make(str()->random(8));

        $record = new ($this->getModel())(Arr::only($data, [
            'name',
            'email',
            'email_verified_at',
            'password',
            'status',
            'language',
        ]));

        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->save();

        $roles = array_filter([
            $data['main_web_role'] ?? null,
            $data['main_api_role'] ?? null,
        ]);

        if ($roles) {
            $record->roles()->sync($roles);
        }

        return $record;
    }
}
