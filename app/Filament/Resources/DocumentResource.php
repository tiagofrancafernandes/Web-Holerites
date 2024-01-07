<?php

namespace App\Filament\Resources;

use App\Enums\DocumentStatus;
use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Filament\Resources\Traits\ModelLabel;


use App\Filament\Resources\Shop\BrandResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\Shop\ProductResource\RelationManagers;
use App\Filament\Resources\Shop\ProductResource\Widgets\ProductStats;
use App\Models\Shop\Product;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\DocumentCategoryResource;
use App\Models\DocumentCategory;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Infolists\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class DocumentResource extends \App\Filament\Resources\Extended\ExtendedResourceBase
{
    use ModelLabel;

    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/navigation.groups.documents');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->disabled(
                                fn(?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->heading('Identificação')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Título do documento')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Document::class, 'slug', ignoreRecord: true)
                                    ->columnSpan(2),
                            ])
                            ->columns(4),

                        Forms\Components\Section::make()
                            ->heading('Nota')
                            ->schema([
                                Forms\Components\MarkdownEditor::make('public_note')
                                    ->label('Nota')
                                    ->helperText('Esse texto poderá ser visto pelo(s) colaborador(es)')
                                    ->maxLength(1500)
                                    ->columnSpanFull(),
                            ])
                            ->disabled(
                                fn(?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->collapsible(),

                        Forms\Components\Section::make()
                            ->heading('Nota interna')
                            ->schema([
                                Forms\Components\Toggle::make('show_internal_note')
                                    ->label('Mostrar nota interna?')
                                    ->live()
                                    ->dehydrated(false),

                                Forms\Components\MarkdownEditor::make('internal_note')
                                    ->label('Nota interna')
                                    ->helperText('Esse texto não poderá ser visto pelo(s) colaborador(es)')
                                    ->hidden(fn(callable $get) => !$get('show_internal_note'))
                                    ->live()
                                    ->maxLength(1500)
                                    ->columnSpanFull(),
                            ])
                            ->disabled(
                                fn(?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->hidden(
                                fn(?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->columns(2)
                            ->collapsible()
                            ->collapsed(),

                        // Forms\Components\Section::make('Visibilidade')
                        //     ->schema([
                        //         Forms\Components\Checkbox::make('backorder')
                        //             ->label('This product can be returned'),

                        //         Forms\Components\Checkbox::make('requires_shipping')
                        //             ->label('This product will be shipped'),
                        //     ])
                        //     ->columns(2)
                        //     ->collapsible()
                        //     ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options(
                                        collect(DocumentStatus::cases())
                                            ->mapWithKeys(fn($enum) => [$enum->value => $enum?->label()])
                                            ->toArray()
                                    )
                                    ->default(DocumentStatus::DRAFT->value)
                                    ->required()
                                    ->hidden(
                                        fn(?Model $record) => !static::allowed(['manage'], $record),
                                    )
                                    ->native(false),

                                Forms\Components\Toggle::make('public')
                                    ->label('Visível?')
                                    ->helperText('Se esse item poderá ser visualizado')
                                    ->default(true)
                                    ->hidden(
                                        fn(?Model $record) => !static::allowed(['manage'], $record),
                                    ),

                                Forms\Components\DatePicker::make('release_date')
                                    ->label('Disponível a partir de')
                                    ->helperText('Data a partir da qual (quando publicado) poderá ser visualizado')
                                    ->default(now())
                                    ->required()
                                    ->hidden(
                                        fn(?Model $record) => !static::allowed(['manage'], $record),
                                    ),

                                Forms\Components\DatePicker::make('available_until')
                                    ->label('Disponível até')
                                    ->helperText('Data até a qual o item poderá ser visualizado'),
                            ])
                            ->hidden(
                                fn(?Model $record) => !static::allowed(['manage'], $record),
                            ),

                        Forms\Components\Section::make('Associações')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->heading('Documento')
                                    ->schema([
                                        FileUpload::make('document_file.path')
                                            ->disabled(
                                                fn(?Model $record) => !static::allowed(['manage'], $record),
                                            )
                                            ->live()
                                            ->visibility('private')
                                            ->label('Arquivo')
                                            ->storeFileNamesIn('document_file.original_name')
                                            // ->directory('documents')
                                            // ->getUploadedFileNameForStorageUsing(
                                            //     function (TemporaryUploadedFile $file, callable $set): string {
                                            //         $set(
                                            //             'document_file.extension',
                                            //             $file->getClientOriginalExtension() ?: pathinfo(
                                            //                 $file->getClientOriginalName(),
                                            //                 PATHINFO_EXTENSION
                                            //             )
                                            //         );

                                            //         return (string) str($file->getClientOriginalName())
                                            //             ->prepend(time() . '-');
                                            //     },
                                            // )
                                            ->disk(static::getDocumentDisk())
                                            ->downloadable()
                                            ->acceptedFileTypes([
                                                'image/png',
                                                'image/jpeg',
                                                'application/pdf',
                                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                            ])
                                            ->hidden(
                                                fn(?Model $record) => boolval($record?->file),
                                            )
                                            ->columnSpanFull(),

                                        Forms\Components\View::make('filament.custom.forms.components.html-record-content')
                                            ->viewData([
                                                'content' => fn(?Model $record) => html()->element('div')
                                                    ->html(
                                                        implode(
                                                            '',
                                                            [
                                                                Tables\Actions\Action::make('open_file')
                                                                    ->label('Abrir anexo')
                                                                    ->url(
                                                                        url: route('storage_documents.show', $record?->file?->path),
                                                                        shouldOpenInNewTab: true,
                                                                    )
                                                                    ->icon('feathericon-external-link')
                                                                    ->hidden(
                                                                        !$record?->file,
                                                                    )->toHtml(),
                                                                Tables\Actions\Action::make('download_file')
                                                                    ->label('Baixar anexo')
                                                                    ->url(
                                                                        url: route(
                                                                            'storage_documents.show',
                                                                            [
                                                                                $record?->file?->path,
                                                                                'download' => true,
                                                                            ],
                                                                        ),
                                                                        shouldOpenInNewTab: true,
                                                                    )
                                                                    ->icon('feathericon-download-cloud')
                                                                    ->hidden(
                                                                        !$record?->file,
                                                                    )->toHtml(),
                                                            ]
                                                        )
                                                    ),
                                            ])
                                            ->hidden(fn(?Model $record) => !$record)
                                            ->dehydrated(false)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Section::make('Associações')
                            ->hidden(
                                fn(?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->schema([
                                // Forms\Components\Select::make('shop_brand_id')
                                //     ->relationship('brand', 'name')
                                //     ->searchable()
                                //     ->hiddenOn(ProductsRelationManager::class),

                                Forms\Components\Select::make('category_id')
                                    ->label('Categoria')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->preload()
                                    ->createOptionForm(DocumentCategoryResource::getFormSchema())
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Create category')
                                            ->modalButton('Create category')
                                            ->modalWidth('3xl');
                                    }),

                                // Forms\Components\Select::make('shop_customer_id')
                                //     ->relationship('customer', 'name')
                                //     ->searchable()
                                //     ->required()
                                //     ->createOptionForm([
                                //         Forms\Components\TextInput::make('name')
                                //             ->required()
                                //             ->maxLength(255),

                                //         Forms\Components\TextInput::make('email')
                                //             ->label('Email address')
                                //             ->required()
                                //             ->email()
                                //             ->maxLength(255)
                                //             ->unique(),

                                //         Forms\Components\TextInput::make('phone')
                                //             ->maxLength(255),

                                //         Forms\Components\Select::make('gender')
                                //             ->placeholder('Select gender')
                                //             ->options([
                                //                 'male' => 'Male',
                                //                 'female' => 'Female',
                                //             ])
                                //             ->required()
                                //             ->native(false),
                                //     ])
                                //     ->createOptionAction(function (Action $action) {
                                //         return $action
                                //             ->modalHeading('Create customer')
                                //             ->modalButton('Create customer')
                                //             ->modalWidth('lg');
                                //     }),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(
                        static::getTableAttributeLabel('id')
                    )
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: false,
                    ),

                Tables\Columns\TextColumn::make('title')
                    ->label(
                        static::getTableAttributeLabel('title')
                    )
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    )
                    ->toggleable(
                        isToggledHiddenByDefault: false,
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label(
                        static::getTableAttributeLabel('status')
                    )
                    ->sortable()
                    ->formatStateUsing(fn(?Model $record) => $record?->status?->label())
                    ->toggleable(
                        isToggledHiddenByDefault: false,
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(
                        static::getTableAttributeLabel('created_at')
                    )
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    )
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(
                        static::getTableAttributeLabel('updated_at')
                    )
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                    )
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filtersTriggerAction(
                fn(\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label(__('Filter')),
            )
            ->filters([
                // Tables\Filters\SelectFilter::make('status')
                //     ->label(
                //         static::getFilterLabel('status')
                //     )
                //     ->options([
                //         0 => 'Waiting',
                //         1 => 'In conversation',
                //         2 => 'Waiting contact',
                //     ]),

                // Tables\Filters\SelectFilter::make('document_category_id')
                //     ->label('Categoria')
                //     ->options(
                //         function () {
                //             $updateCache = request()->boolean('updateCache', false);

                //             $categories = DocumentCategory::tabList($updateCache);

                //             return $categories?->pluck('name', 'id')
                //                     ?->unique()
                //                     ?->toArray() ?? [];
                //         }
                //     )
                //     ->native(false)
                //     ->searchable(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Cadastrado a partir de'),
                            DatePicker::make('created_until')
                            ->label('Cadastrado até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(static::getActionLabel('edit')),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('open_file')
                    ->label('Abrir anexo')
                    ->url(
                        url: fn(?Model $record) => route('storage_documents.show', $record?->file?->path),
                        shouldOpenInNewTab: true,
                    )
                    ->icon('feathericon-external-link')
                    ->hidden(
                        fn(?Model $record) => !$record?->file
                        || !(
                            ($record?->status === DocumentStatus::PUBLISHED)
                            || static::allowed(['manage'], $record)
                        ),
                    ),
                Tables\Actions\Action::make('download_file')
                    ->label('Baixar anexo')
                    ->url(
                        url: fn(?Model $record) => route(
                            'storage_documents.show',
                            [
                                $record?->file?->path,
                                'download' => true,
                            ],
                        ),
                        shouldOpenInNewTab: true,
                    )
                    ->icon('feathericon-download-cloud')
                    ->hidden(
                        fn(?Model $record) => !$record?->file,
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

    public static function getDocumentDisk(): string
    {
        return (string) (
            config('documents.storage.default_disk') ?: config('filesystems.default')
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'manage' => Pages\ManageDocuments::route('/manage'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
            'details' => Pages\ViewDocument::route('/{record}/view'),
            // 'view' => Pages\ViewDocument::route('/{record}/view'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('title')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('slug')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('status')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('release_date')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('available_until')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('storage_file_id')
                    ->url(
                        url: fn(?Model $record) => $record?->file?->url,
                        shouldOpenInNewTab: true,
                    )
                    ->label('Documento')
                    ->formatStateUsing(fn() => 'Abrir documento')
                    ->icon('feathericon-external-link')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('created_by')
                    ->label('Cadastrado por')
                    ->formatStateUsing(fn(?Model $record) => $record?->creator?->name)
                    ->inlineLabel(),

                Infolists\Components\IconEntry::make('public')
                    ->boolean()
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('document_category_id')
                    ->inlineLabel(),


                Section::make('Nota interna')
                    ->description('Essa nota não será visível ao(s) colaborador(es)')
                    ->schema([
                        Infolists\Components\TextEntry::make('internal_note')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed(false)
                    ->id('infolist_internal_note')
                    ->columnSpanFull(),

                Section::make('Nota')
                    ->description('Nota que será visível ao(s) colaborador(es)')
                    ->schema([
                        Infolists\Components\TextEntry::make('public_note')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed(false)
                    ->id('infolist_public_note')
                    ->columnSpanFull(),
            ]);
    }
}
