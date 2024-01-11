<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use App\Filament\Resources\RoleResource;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Role;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->unique(Permission::class, 'name', ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                    ),
            ])
            ->filtersTriggerAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label(__('Filter')),
            )
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(fn () => Role::clearCache()),

                \App\Filament\Forms\Tables\Actions\AttachAction::make()
                    ->attachMultiple()
                    ->hidden(fn () => !RoleResource::allowed('permission::can_attach', toAddPrefix: false))
                    ->after(fn () => Role::clearCache())
                    ->preloadRecordSelect(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make()
                //     ->label(static::getResource()::getActionLabel('edit')),
                Tables\Actions\DetachAction::make()
                    ->hidden(fn () => !RoleResource::allowed('permission::can_detach', toAddPrefix: false))
                    ->after(fn () => Role::clearCache()),
                // Tables\Actions\DeleteAction::make()
                //     ->label(static::getActionLabel('delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                    ->hidden(fn () => !RoleResource::allowed('permission::can_detach', toAddPrefix: false))
                    ->after(fn () => Role::clearCache()),
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
