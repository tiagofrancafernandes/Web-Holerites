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
use Guava\FilamentIconPicker\Layout;
use App\Filament\Extended\Filament\Forms\IconPicker;
use App\Filament\Extended\Filament\Tables\IconColumn;

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
                ->collapsed(fn(?Model $record) => !$record?->parent_id)
                ->columnSpanFull(),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->readOnly(fn(?Model $record) => boolval($record?->is_canonical))
                        ->disabled(fn(?Model $record) => boolval($record?->is_canonical))
                        ->helperText(
                            fn(?Model $record) => boolval($record?->is_canonical)
                            ? __('models.general.texts.edition_disabled_because_is_canonical') : ''
                        )
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
                        ->unique(DocumentCategory::class, 'name', ignoreRecord: true)
                        ->columnSpan(3),

                    Forms\Components\TextInput::make('slug')
                        ->label(
                            static::getTableAttributeLabel('slug')
                        )
                        ->disabled()
                        ->reactive()
                        ->dehydrated()
                        ->required()
                        ->maxLength(255)
                        ->unique(DocumentCategory::class, 'slug', ignoreRecord: true)
                        ->columnSpan(3),

                    IconPicker::make('icon')
                        ->label(
                            static::getFormAttributeLabel('icon')
                        )
                        ->layout(Layout::ON_TOP)
                        ->selectablePlaceholder()
                        ->placeholderText('Selecione um ícone')
                        ->columnSpanFull(),

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
                ])
                ->columns(6)
                ->columnSpanFull(),

            Forms\Components\Section::make()
                ->heading(__('Visibility'))
                ->schema([
                    Forms\Components\Toggle::make('show_on_tab_filter')
                        ->label(
                            static::getTableAttributeLabel('show_on_tab_filter')
                        )
                        ->inline(false)
                        ->inlineLabel(false)
                        ->dehydrated()
                        ->columnSpan(3),

                    Forms\Components\TextInput::make('order_on_tab_filter')
                        ->label(
                            static::getTableAttributeLabel('order_on_tab_filter')
                        )
                        ->numeric()
                        ->dehydrated()
                        ->columnSpan(3),
                ])
                ->collapsed()
                ->columns(6)
                ->columnSpanFull(),

            Forms\Components\Section::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('seo_title')
                        ->label(
                            static::getTableAttributeLabel('seo_title')
                        )
                        ->maxLength(60)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('seo_description')
                        ->label(
                            static::getTableAttributeLabel('seo_description')
                        )
                        ->maxLength(160)
                        ->columnSpanFull(),
                ])
                ->collapsed()
                ->columns(6)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('icon')
                    ->hideIcon(fn(?Model $record) => !$record?->icon)
                    ->useIcon('feathericon-tag')
                    ->useIcon(fn(?Model $record) => $record?->icon)
                    ->label(
                        static::getTableAttributeLabel('icon')
                    ),

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
                        isGlobal: false,
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
                fn(\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label(__('Filter')),
            )
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(static::getActionLabel('edit'))
                    ->hidden(
                        fn(?Model $record) => !static::allowed(
                            ['edit', 'editAny'],
                            $record
                        )
                    )->after(fn() => DocumentCategory::tabList(true)),

                Tables\Actions\DeleteAction::make()
                    ->label(static::getActionLabel('delete'))
                    ->hidden(
                        fn(?Model $record) => boolval($record?->is_canonical) || !static::allowed(
                            ['delete', 'deleteAny'],
                            $record
                        )
                    )->after(fn() => DocumentCategory::tabList(true)),

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
