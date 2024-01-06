<?php

namespace App\Filament\Concerns\Default;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class DefaultPageActions
{
    /**
     * @return array
     */
    public static function getPageHeaderActions(
        string|object $resource,
        array $aditionalParams = [],
    ): array {
        $resourcePages = PageHelpers::resourcePages($resource);

        return [
            \Filament\Actions\EditAction::make()
                ->hidden(
                    fn (?Model $record): bool => !$record
                        || !array_key_exists('edit', $resourcePages)
                        || PageHelpers::resourceUrlIsCurrent(
                            $resource,
                            'edit',
                            ['record' => $record]
                        )
                )
                ->disabled(
                    fn (?Model $record): bool => !$record
                        || !array_key_exists('edit', $resourcePages)
                        || PageHelpers::resourceUrlIsCurrent(
                            $resource,
                            'edit',
                            ['record' => $record]
                        )
                ),
            \Filament\Actions\DeleteAction::make()
                ->hidden(fn (?Model $record): bool => !$record),

            Actions\Action::make('custom_create')
                ->label(__('Create new item'))
                ->url(
                    fn () => PageHelpers::resourceGetUrl($resource, 'create')
                )
                ->hidden(
                    fn (?Model $record): bool => !$record
                        || !array_key_exists('edit', $resourcePages)
                        || PageHelpers::resourceUrlIsCurrent(
                            $resource,
                            'create',
                            ['record' => $record]
                        )
                )
                ->disabled(
                    fn (?Model $record): bool => !$record
                        || !array_key_exists('create', $resourcePages)
                        || PageHelpers::resourceUrlIsCurrent(
                            $resource,
                            'create',
                            ['record' => $record]
                        )
                ),
        ];
    }
}
