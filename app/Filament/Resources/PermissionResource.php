<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Helpers\Traits\PermissionHelpers;
use App\Filament\Resources\Traits\ModelLabel;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;

class PermissionResource extends \App\Filament\Resources\Extended\ExtendedResourceBase
{
    use PermissionHelpers;
    use ModelLabel;

    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'codicon-shield';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/navigation.groups.user_control');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->icon('codicon-shield')
                    ->label(__('models.Permission.table.name'))
                    ->copyable()
                    ->copyMessage('Email address copied')
                    ->copyMessageDuration(1500)
                    ->inlineLabel()->columnSpanFull(),

                Infolists\Components\TextEntry::make('guard_name')
                    ->label(__('models.Permission.table.guard_name'))
                    ->inlineLabel()->columnSpanFull(),

                Infolists\Components\TextEntry::make('roles_count')
                    ->label(__('models.Permission.table.roles_count'))
                    ->inlineLabel()->columnSpanFull(),

                // Infolists\Components\TextEntry::make('notes')
                // ->columnSpanFull(),
            ]);
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
                    ->label(__('models.Permission.form.guard_name'))
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
                    )
                    ->toggleable(isToggledHiddenByDefault: false),

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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePermissions::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('roles');
    }
}
