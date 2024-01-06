<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentCategoryResource\Pages;
use App\Filament\Resources\DocumentCategoryResource\RelationManagers;
use App\Models\Document;
use App\Models\DocumentCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\Traits\ModelLabel;

class DocumentCategoryResource extends \App\Filament\Resources\Extended\ExtendedResourceBase
{
    use ModelLabel;

    protected static ?string $model = DocumentCategory::class;

    protected static ?string $navigationIcon = 'feathericon-tag';

    public static function getNavigationGroup(): ?string
    {
        return 'Documentos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormSchema())
            ->columns(3);
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make()
                ->heading(
                    static::getTableAttributeLabel('parent_id')
                )
                ->schema([
                    Forms\Components\Select::make('parent_id')
                        ->label(
                            static::getTableAttributeLabel('parent_id')
                        )
                        ->relationship('parent', 'name')
                        ->preload()
                        ->searchable(),
                ])
                ->collapsible()
                ->collapsed(fn (?Model $record) => !$record?->parent_id)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('name')
                ->label(static::getTableAttributeLabel('name'))
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                    if (!in_array($operation, ['create', 'createOption'])) {
                        return;
                    }

                    $set('slug', Str::slug($state));
                    $set('seo_title', Str::ucfirst($state));
                    $set('name', Str::ucfirst($state));
                })
                ->unique(DocumentCategory::class, 'name', ignoreRecord: true),

            Forms\Components\TextInput::make('slug')
                ->label(
                    static::getTableAttributeLabel('slug')
                )
                ->disabled()
                ->reactive()
                ->dehydrated()
                ->required()
                ->maxLength(255)
                ->unique(DocumentCategory::class, 'slug', ignoreRecord: true),

            Forms\Components\TextInput::make('seo_title')
                ->label(
                    static::getTableAttributeLabel('seo_title')
                )
                ->maxLength(60),

            Forms\Components\Textarea::make('description')
                ->label(
                    static::getTableAttributeLabel('description')
                )
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                    if (!in_array($operation, ['create', 'createOption'])) {
                        return;
                    }

                    $set('seo_description', $state);
                })
                ->columnSpanFull(),

            Forms\Components\Textarea::make('seo_description')
                ->label(
                    static::getTableAttributeLabel('seo_description')
                )
                ->maxLength(160)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(
                        static::getTableAttributeLabel('name')
                    )
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                    ),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(
                        static::getTableAttributeLabel('parent_name')
                    )
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('slug')
                    ->label(
                        static::getTableAttributeLabel('slug')
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('seo_title')
                    ->label(
                        static::getTableAttributeLabel('seo_title')
                    )
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('seo_description')
                    ->label(
                        static::getTableAttributeLabel('seo_description')
                    )
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(
                        static::getTableAttributeLabel('created_at')
                    )
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(
                        static::getTableAttributeLabel('updated_at')
                    )
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(
                        static::getTableAttributeLabel('deleted_at')
                    )
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                ->label(static::getActionLabel('delete')),
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
            'index' => Pages\ListDocumentCategories::route('/'),
            // 'create' => Pages\CreateDocumentCategory::route('/create'),
            // 'edit' => Pages\EditDocumentCategory::route('/{record}/edit'),
        ];
    }
}
