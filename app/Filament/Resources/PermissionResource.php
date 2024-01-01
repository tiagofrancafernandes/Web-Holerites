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

class PermissionResource extends Resource
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
                ->label(__('filament/resources.Permission.table.name'))
                ->copyable()
                    ->copyMessage('Email address copied')
                    ->copyMessageDuration(1500)
                    ->inlineLabel()->columnSpanFull(),

                Infolists\Components\TextEntry::make('guard_name')
                ->label(__('filament/resources.Permission.table.guard_name'))
                ->inlineLabel()->columnSpanFull(),

                Infolists\Components\TextEntry::make('roles_count')
                ->label(__('filament/resources.Permission.table.roles_count'))
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
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('guard_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
            ->sortable()
            ->searchable(isIndividual: true),

                Tables\Columns\TextColumn::make('guard_name')
            ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Actions\EditAction::make()
                    ->label(static::getActionLabel('edit')),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
