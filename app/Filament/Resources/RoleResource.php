<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Traits\ModelLabel;

class RoleResource extends Resource
{
    use ModelLabel;

    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'gmdi-badge-r';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/navigation.groups.user_control');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('guard_name')
                    ->required()
                    ->options([
                        'web' => 'web',
                        'api' => 'api',
                    ])
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('guard_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('permissionCount')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersTriggerAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label(__('Filter')),
            )
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make()
                //     ->label(static::getResource()::getActionLabel('edit')),
                // Tables\Actions\DeleteAction::make(),
                // Tables\Actions\CreateAction::make()
                //     ->label(static::getResource()::getActionLabel('create')),
            ])
            // ->paginated([10, 25, 50, 100, 'all'])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PermissionsRelationManager::class,
        ];
    }
}
