<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PermissionResource;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';
    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.User.Relations.permissions.title');
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
                Tables\Columns\TextColumn::make('name')
                    ->label(__('models.Permission.table.name'))
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                    ),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label(__('models.Permission.table.guard_name'))
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->hidden(fn() => !PermissionResource::allowed('permission::can_attach', toAddPrefix: false))
                    ->after(fn() => Role::clearCache())
                    ->preloadRecordSelect()
                    ->label(__('models.Permission.actions.attach')),
            ])
            ->actions([
                // Tables\Actions\DeleteAction::make()
                //     ->label(static::getActionLabel('delete')),
                // Tables\Actions\EditAction::make()

                //     ->label(static::getResource()::getActionLabel('edit')),

                Tables\Actions\DetachAction::make()
                    ->hidden(fn() => !PermissionResource::allowed('permission::can_detach', toAddPrefix: false))
                    ->after(fn() => Role::clearCache())
                    ->label(__('models.Permission.actions.detach')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                    ->hidden(fn() => !PermissionResource::allowed('permission::can_detach', toAddPrefix: false))
                    ->after(fn() => Role::clearCache()),
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
