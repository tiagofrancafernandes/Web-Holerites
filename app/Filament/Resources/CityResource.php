<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\Traits\ModelLabel;

class CityResource extends \App\Filament\Resources\Extended\ExtendedResourceBase
{
    use ModelLabel;

    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'fas-city';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return __('filament/navigation.groups.global');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('city_code')
                    ->numeric(),

                Forms\Components\TextInput::make('state_code')
                    ->maxLength(255),

                Forms\Components\TextInput::make('state_name')
                    ->maxLength(255),

                Forms\Components\TextInput::make('state_local_name')
                    ->maxLength(255),

                Forms\Components\TextInput::make('country_iso_code')
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

                Tables\Columns\TextColumn::make('city_code')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (?Model $record) => $record ? "{$record?->city_code}" : '')
                    ->searchable(
                        query: function (Builder $query, string $search) {
                            $search = trim($search);

                            if (!$search || !is_numeric($search)) {
                                return $query->where('id', 'ilike', 0);
                            }

                            return $query->where('city_code', 'ilike', $search . '%');
                        },
                        isIndividual: true
                    ),

                Tables\Columns\TextColumn::make('state_code')
                    ->sortable()
                    ->searchable(
                        query: function (Builder $query, string $search) {
                            $search = trim($search);

                            if (!$search || strlen($search) >= 3) {
                                return $query->where('id', 'ilike', 0);
                            }

                            return $query->where('state_code', 'ilike', $search . '%');
                        },
                        isIndividual: true
                    ),

                Tables\Columns\TextColumn::make('state_name')
                    ->sortable()
                    ->searchable(
                        query: function (Builder $query, string $search) {
                            $search = trim($search);

                            if (!$search) {
                                return $query->where('id', 'ilike', 0);
                            }

                            return $query->where('state_name', 'ilike', '%' . $search . '%');
                        },
                        isIndividual: true
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('state_local_name')
                    ->sortable()
                    ->searchable(
                        query: function (Builder $query, string $search) {
                            $search = trim($search);

                            if (!$search) {
                                return $query->where('id', 'ilike', 0);
                            }

                            return $query->where('state_local_name', 'ilike', '%' . $search . '%');
                        },
                        isIndividual: true
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('country_iso_code')
                    ->sortable()
                    ->searchable(
                        query: function (Builder $query, string $search) {
                            $search = trim($search);

                            if (!$search || strlen($search) >= 3) {
                                return $query->where('id', 'ilike', 0);
                            }

                            return $query->where('country_iso_code', 'ilike', $search . '%');
                        },
                        isIndividual: true
                    )
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
                    ->label(static::getActionLabel('edit'))
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
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record:city_code}/edit'),
        ];
    }
}
