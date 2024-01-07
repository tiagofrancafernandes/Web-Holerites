<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\GroupResource;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.User.Relations.groups.title');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->hidden(fn() => !GroupResource::allowed('group::can_attach', toAddPrefix: false))
                    ->preloadRecordSelect()
                    ->label(__('models.User.Relations.groups.attach')),
            ])
            ->actions([
                // Tables\Actions\DeleteAction::make()
                //     ->label(static::getActionLabel('delete')),
                // Tables\Actions\EditAction::make()

                //     ->label(static::getResource()::getActionLabel('edit')),

                Tables\Actions\DetachAction::make()
                    ->hidden(fn() => !GroupResource::allowed('group::can_detach', toAddPrefix: false))
                    ->label(__('models.User.Relations.groups.detach')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                    ->hidden(fn() => !GroupResource::allowed('group::can_detach', toAddPrefix: false))
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
