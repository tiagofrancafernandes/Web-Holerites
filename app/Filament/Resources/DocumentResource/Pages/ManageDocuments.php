<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use Filament\Actions;
use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use App\Enums\DocumentStatus;

class ManageDocuments extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = DocumentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(static::getResource()::getActionLabel('create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return DocumentResource::getWidgets();
    }

    public function getTabs(): array
    {
        $authUser = auth()->user();
        $updateCache = request()->boolean('updateCache') ?? request()->boolean('noCache', false);

        $userDocumentStatusAllowed = cache()
            ->remember(
                'userDocumentStatusAllowed-' . $authUser?->id,
                60,
                fn() => $authUser->getAllCachedPermissionsName($updateCache)
                    ?->filter(fn($item) => str_contains($item, 'document_status::see.'))
                    ?->values()
                    ?->toArray()
            );

        return collect(DocumentStatus::cases())
                ->filter(fn ($item) => in_array("document_status::see.{$item?->name}", $userDocumentStatusAllowed))
                ->mapWithKeys(fn($enum) => [
                    $enum->name => Tab::make()
                        ->query(
                            fn($query) => $query->where('status', $enum->value)
                        )->label($enum?->label())
                ])
                ->prepend(
                    Tab::make('All')->label(__('All')),
                    'ALL'
                )
                ->toArray();
    }
}
