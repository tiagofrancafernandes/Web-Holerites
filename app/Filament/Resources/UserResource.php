<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Traits\ModelLabel;

class UserResource extends Resource
{
    use ModelLabel;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/navigation.groups.user_control');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('id')
                //     ->label(static::getFormAttributeLabel('id'))
                //     ->hidden()
                //     ->required(),

                Forms\Components\TextInput::make('name')
                    ->label(static::getFormAttributeLabel('name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label(static::getFormAttributeLabel('email'))
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(static::getFormAttributeLabel('email_verified_at')),

                Forms\Components\TextInput::make('password')
                    ->label(static::getFormAttributeLabel('password'))
                    ->password()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('status')
                    ->label(static::getFormAttributeLabel('status'))
                    ->required()
                    ->numeric()
                    ->default(1),

                Forms\Components\TextInput::make('language')
                    ->label(static::getFormAttributeLabel('language'))
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(static::getTableAttributeLabel('id'))
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label(static::getTableAttributeLabel('name'))
                    ->sortable()
                    ->searchable(
                        isGlobal: false,
                        isIndividual: true,
                    ),

                Tables\Columns\TextColumn::make('email')
                    ->label(static::getTableAttributeLabel('email'))
                    ->sortable()
                    ->searchable(
                        isGlobal: false,
                        isIndividual: true,
                    ),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(static::getTableAttributeLabel('email_verified_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label(static::getTableAttributeLabel('status'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('language')
                    ->label(static::getTableAttributeLabel('language'))
                    ->searchable(
                        isGlobal: false,
                        isIndividual: true,
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(static::getTableAttributeLabel('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(static::getTableAttributeLabel('updated_at'))
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(static::getActionLabel('edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
