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
use Illuminate\Database\Eloquent\Model;

class RoleResource extends \App\Filament\Resources\Extended\ExtendedResourceBase
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
                    ->readOnly(fn(?Model $record) => boolval($record?->is_canonical))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('guard_name')
                    ->disabled(fn(?Model $record) => boolval($record?->is_canonical))
                    ->helperText(
                        fn(?Model $record) => boolval($record?->is_canonical)
                        ? __('models.general.texts.edition_disabled_because_is_canonical') : ''
                    )
                    ->label(__('models.Role.form.guard_name'))
                    ->placeholder('Select a guard name')
                    ->options([
                        'web' => 'WEB',
                        'api' => 'API',
                    ])
                    ->default('web')
                    ->required()
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                    ),

                Tables\Columns\TextColumn::make('guard_name')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                    ),

                Tables\Columns\TextColumn::make('permissionCount'),

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
                fn(\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label(__('Filter')),
            )
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(static::getActionLabel('edit'))
                    ->hidden(
                        fn(?Model $record) => !static::allowed(
                            ['edit', 'editAny'],
                            $record
                        )
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label(static::getActionLabel('delete'))
                    ->hidden(
                        fn(?Model $record) => boolval($record?->is_canonical) || !static::allowed(
                            ['delete', 'deleteAny'],
                            $record
                        )
                    ),

                Tables\Actions\ViewAction::make()
                    ->label(static::getActionLabel('view'))
                    ->hidden(
                        fn(?Model $record) => !static::allowed(
                            ['view', 'viewAny'],
                            $record
                        )
                    ),
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
