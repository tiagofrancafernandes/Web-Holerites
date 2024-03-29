<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use Filament\Actions;
use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Facades\Filament;

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
        $updateCache = boolval(request()->input('updateCache') ?? request()->input('noCache', false));

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

    public function getTitle(): string | Htmlable
    {
        return static::getResource()::getTranslatedDotLabel('models.Document.pages.ManageDocuments.title');
    }

    protected function authorizeAccess(): void
    {
        abort_unless(
            Filament::auth()?->user()?->can([
                'document::viewAny',
                'document::manage',
            ]),
            403
        );
    }

    public function table(Table $table): Table
    {
        /**
         * @var Table $table
         */
        $table = static::getResource()::table($table);

        return $table->modifyQueryUsing(function (Builder $query) {
            return $query;
        });
    }
}
