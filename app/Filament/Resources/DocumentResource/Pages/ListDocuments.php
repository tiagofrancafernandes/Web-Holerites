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
use Livewire\Attributes\Url;

class ListDocuments extends ListRecords
{
    use ExposesTableToWidgets;

    /**
     * @var array<string, mixed> | null
     */
    #[Url]
    public ?array $tableFilters = null;

    /**
     * @var string | null
     */
    #[Url]
    public ?string $activeTab = null;

    protected static string $resource = DocumentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('manage')
                ->url(static::getResource()::getUrl('manage'))
                ->label(static::getResource()::getActionLabel('manage'))
                ->hidden(
                    fn() => !static::getResource()::allowed('manage')
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
        $updateCache = boolval(request()->input('updateCache') ?? request()->input('noCache', false));

        $categories = DocumentCategory::tabList($updateCache);

        return collect($categories)
            ->mapWithKeys(function (object $category) {

                $tab = Tab::make()
                    ->label($category?->name)
                    ->badge($category?->count)
                    ->query(
                    fn($query) => $query->where('document_category_id', $category->id)
                );

                if ($category?->icon) {
                    $tab = $tab->icon($category?->icon);
                }

                return [
                    $category->slug => $tab,
                ];
            })
            ->prepend(
                Tab::make('All')
                    ->label(__('All'))
                    ->badge(
                        $categories
                            ?->pluck('count')
                            ?->sum()?: ''
                    ),
                'ALL'
            )
            ->toArray();
    }
}
