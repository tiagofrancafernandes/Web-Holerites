<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HoleriteResource\Pages;
use App\Models\Holerite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class HoleriteResource extends Resource
{
    protected static ?string $model = Holerite::class;

    protected static ?string $navigationIcon = 'tabler-report-money';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\MarkdownEditor::make('content')
                    ->maxLength(1500)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    ),

                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    ),

                Tables\Columns\TextColumn::make('updated_at')
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        0 => 'Waiting',
                        1 => 'In conversation',
                        2 => 'Waiting contact',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListHolerites::route('/'),
            'create' => Pages\CreateHolerite::route('/create'),
            'edit' => Pages\EditHolerite::route('/{record}/edit'),
            'details' => Pages\ViewHolerite::route('/{record}/view'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('email'),
                Infolists\Components\TextEntry::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
