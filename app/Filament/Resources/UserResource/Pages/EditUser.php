<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Illuminate\Support\Arr;

/**
 * @property ?array $data
 */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    #[Locked]
    public Model | int | string | null $record;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(
                    function(Model $record): bool {
                        if ($record?->is_canonical) {
                            return true;
                        }

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

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->data = array_merge(
            $this->data,
            [
                'main_web_role' => $this->record?->mainWebRole?->id,
                'main_api_role' => $this->record?->mainApiRole?->id,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(Arr::only($data, [
            'name',
            'email',
            'email_verified_at',
            'password',
            'status',
            'language',
        ]));

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
