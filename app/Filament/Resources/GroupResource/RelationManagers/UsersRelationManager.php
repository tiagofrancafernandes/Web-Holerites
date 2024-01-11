<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Filament\Resources\GroupResource;
use App\Filament\Resources\UserResource;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

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
                Tables\Columns\TextColumn::make('id')
                    ->label(GroupResource::getTableAttributeLabel('id'))
                    ->sortable(['users.id'])
                    ->searchable(
                        isIndividual: true,
                    )
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('name')
                    ->label(GroupResource::getTableAttributeLabel('name'))
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                    )
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \App\Filament\Forms\Tables\Actions\AttachAction::make()
                    ->attachMultiple()
                    ->label(GroupResource::getActionLabel('attach')),

                Tables\Actions\CreateAction::make()
                    ->label(UserResource::getActionLabel('create')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->url(
                    url: fn (?Model $record) => UserResource::getUrl('edit', [
                        'record' => $record?->id,
                    ]),
                ),
                Tables\Actions\DeleteAction::make()
                    ->label(UserResource::getActionLabel('delete')),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        // ->headerActions([
        //     Tables\Actions\CreateAction::make(),
        //     \App\Filament\Forms\Tables\Actions\AttachAction::make(),
        // ])
        // ->actions([
        //     // Tables\Actions\EditAction::make()
        //     //     ->label(static::getResource()::getActionLabel('edit')),
        //     Tables\Actions\DetachAction::make(),
        //     // Tables\Actions\DeleteAction::make()
        //     ->label(static::getActionLabel('delete')),
        // ])
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DetachBulkAction::make(),
        //         // Tables\Actions\DeleteBulkAction::make(),
        //     ]),
        // ])
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('id')
                    ->label('ID')
                    ->inlineLabel()
                    ->columnSpan(2),

                Infolists\Components\TextEntry::make('status')
                    ->inlineLabel()
                    ->columnSpan(2),

                Infolists\Components\TextEntry::make('id')
                    ->url(
                        url: fn (?Model $record) => UserResource::getUrl('edit', [
                            'record' => $record?->id,
                        ]),
                    )
                    ->label('')
                    ->formatStateUsing(fn () => 'Editar usuário')
                    ->icon('feathericon-edit')
                    ->inlineLabel()
                    ->columnSpan(2),

                Infolists\Components\TextEntry::make('name')
                    ->inlineLabel()
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('email')
                    ->inlineLabel()
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Cadastrado em')
                    ->formatStateUsing(fn (?Model $record) => $record?->created_at?->format('d/m/Y H:i'))
                    ->inlineLabel()
                    ->columnSpan(3),

                Infolists\Components\TextEntry::make('updated_at')
                    ->label('Atualizado em')
                    ->formatStateUsing(fn (?Model $record) => $record?->updated_at?->format('d/m/Y H:i'))
                    ->inlineLabel()
                    ->columnSpan(3),
            ])
            ->columns(6);
    }
}
