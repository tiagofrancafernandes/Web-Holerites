<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use Filament\Actions;
use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use App\Enums\DocumentStatus;
use App\Models\DocumentCategory;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Model;

class ListDocuments extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = DocumentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('manage')
                ->url(static::getResource()::getUrl('manage'))
                ->label(static::getResource()::getActionLabel('manage'))
                ->hidden(
                    fn(?Model $record) => !static::getResource()::allowed(
                        ['document::manage'],
                        $record
                    )
                ),

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
        $updateCache = request()->boolean('updateCache', false);

        return collect(DocumentCategory::tabList($updateCache))
            ->mapWithKeys(function (DocumentCategory $category) {

                $tab = Tab::make()
                    ->query(
                        fn($query) => $query->where('document_category_id', $category->id)
                    )
                    ->label($category?->name);

                if ($category?->icon) {
                    $tab = $tab->icon($category?->icon);
                }

                return [
                    $category->slug => $tab,
                ];
            })
            ->prepend(
                Tab::make('All')->label(__('All')),
                'ALL'
            )
            ->toArray();
    }
}
