<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Filament\Resources\Traits\ModelLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Actions\Action;
use App\Models\DocumentCategory;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Infolists\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Facades\Filament;
use App\Enums\DocumentVisibleToType;
use App\Models\User;
use App\Models\Group;
use App\Models\StorageFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Enums\DocumentStatus;

class DocumentResource extends Extended\ExtendedResourceBase
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
                                fn (?Model $record): bool => !static::allowed(['manage'], $record)
                                || ($record?->status === DocumentStatus::PUBLISHED)
                            )
                            ->dehydrated(
                                fn ($state, ?Model $record): bool => ($record?->status === DocumentStatus::PUBLISHED)
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

                                        $set('slug', str($state)->slug()->prepend('-')->prepend(uniqid())->slug());
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('slug')
                                    ->label('UID')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Document::class, 'slug', ignoreRecord: true)
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),

                        Forms\Components\Section::make()
                            ->heading('Documento')
                            ->schema([
                                Forms\Components\Toggle::make('without_file')
                                    ->label('Sem arquivo')
                                    ->helperText('Útil em fase de edição ou quando tem apenas texto/nota.')
                                    ->live()
                                    ->disabled(fn (?Model $record) => !static::allowed(['manage'], $record))
                                    ->hidden(function (?Model $record, string $operation, $state, Forms\Set $set) {
                                        if (
                                            !in_array($operation, ['create', 'edit'])
                                            || !static::allowed(['manage'], $record)
                                            // || ($record?->status === DocumentStatus::PUBLISHED)
                                            || $record?->file
                                        ) {
                                            return true;
                                        }

                                        return false;
                                    })
                                    ->default(false)
                                    ->dehydrated(false),

                                FileUpload::make('document_file.path')
                                    ->disabled(fn (?Model $record) => !static::allowed(['manage'], $record))
                                    ->hidden(function (?Model $record, string $operation, $state, Forms\Set $set, Forms\Get $get) {
                                        if (
                                            (bool) $get('without_file')
                                            || !in_array($operation, ['create', 'edit'])
                                            || !static::allowed(['manage'], $record)
                                            || ($record?->status === DocumentStatus::PUBLISHED)
                                        ) {
                                            return true;
                                        }

                                        return false;
                                    })
                                    ->required(fn (callable $get) => !$get('without_file'))
                                    ->live()
                                    ->visibility('private')
                                    ->label('Arquivo')
                                    // ->storeFileNamesIn('document_file.original_name')
                                    // // ->directory('documents')
                                    ->getUploadedFileNameForStorageUsing(
                                        function (TemporaryUploadedFile $file, callable $set): string {
                                            $set(
                                                'document_file.extension',
                                                $file->getClientOriginalExtension() ?: pathinfo(
                                                    $file->getClientOriginalName(),
                                                    PATHINFO_EXTENSION
                                                )
                                            );

                                            $newName = (string) str($file->getClientOriginalName())
                                                ->prepend(time() . '-');

                                            $fileOriginalName = $file?->getClientOriginalName();
                                            $realPath = $file?->getRealPath();
                                            $fileExtension = $file?->getClientOriginalExtension() ?: pathinfo(
                                                $realPath,
                                                PATHINFO_EXTENSION
                                            );

                                            $filePath = $newName;

                                            $set('document_file.clientOriginalName', $file?->getClientOriginalName());
                                            $set('document_file.size', $file?->getSize());
                                            $set('document_file.mimeType', $file?->getMimeType());
                                            $set('document_file.realPath', $realPath);
                                            $set('document_file.clientOriginalExtension', $file?->getClientOriginalExtension());
                                            $set('document_file.fileOriginalName', $fileOriginalName);
                                            $set('document_file.filePath', $filePath);
                                            $set('document_file.fileExtension', $fileExtension);
                                            $set('document_file.fileName', $newName);
                                            $set('document_file.newName', $newName);
                                            $set('document_file.diskName', DocumentResource::getDocumentDisk());

                                            return $newName;
                                        },
                                    )
                                    ->disk(static::getDocumentDisk())
                                    // ->downloadable()
                                    ->acceptedFileTypes([
                                        'image/png',
                                        'image/jpeg',
                                        'application/pdf',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    ])
                                    ->hidden(
                                        fn (?Model $record) => boolval($record?->file),
                                    )
                                    ->columnSpanFull(),

                                Forms\Components\View::make('filament.custom.forms.components.html-record-content')
                                    ->viewData([
                                        'content' => fn (?Model $record) => html()->element('div')
                                            ->html(
                                                implode(
                                                    '',
                                                    [
                                                        $record?->file
                                                        ? Tables\Actions\Action::make('open_file')
                                                            ->label('Abrir anexo')
                                                            ->url(
                                                                url: route('storage_documents.show', $record?->file?->path),
                                                                shouldOpenInNewTab: true,
                                                            )
                                                            ->icon('feathericon-external-link')
                                                            ->hidden(
                                                                !$record?->file,
                                                            )->toHtml() : '',

                                                        $record?->file
                                                        ? Tables\Actions\Action::make('download_file')
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
                                                            )->toHtml() : '',
                                                    ]
                                                )
                                            ),
                                    ])
                                    ->hidden(fn (?Model $record) => !$record)
                                    ->dehydrated(false)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

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
                                fn (?Model $record) => !static::allowed(['manage'], $record),
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
                                    ->hidden(fn (callable $get) => !$get('show_internal_note'))
                                    ->live()
                                    ->maxLength(1500)
                                    ->columnSpanFull(),
                            ])
                            ->disabled(
                                fn (?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->hidden(
                                fn (?Model $record) => !static::allowed(['manage'], $record),
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
                    ->dehydrated(
                        fn ($state, ?Model $record): bool => ($record?->status === DocumentStatus::PUBLISHED)
                    )
                    ->schema([
                        Forms\Components\Section::make('Documento destinado a')
                            ->schema([
                                Forms\Components\Select::make('visible_to_type')
                                    ->label('Documento destinado a')
                                    ->options(
                                        collect(DocumentVisibleToType::cases())
                                            ->mapWithKeys(fn ($enum) => [$enum->value => $enum?->label()])
                                            ->toArray()
                                    )
                                    // ->default(DocumentVisibleToType::USER->value)
                                    ->required()
                                    ->hidden(
                                        fn (?Model $record) => !static::allowed(['manage'], $record),
                                    )
                                    ->live()
                                    ->native(false),

                                Forms\Components\Select::make('visible_to_user')
                                    ->label(__('User'))
                                    ->live()
                                    ->visible(
                                        fn (callable $get) => in_array(
                                            $get('visible_to_type'),
                                            [
                                                DocumentVisibleToType::USER,
                                                DocumentVisibleToType::USER->value,
                                                strval(DocumentVisibleToType::USER->value),
                                            ]
                                        )
                                    )
                                    ->required(
                                        fn (callable $get) => boolval(
                                            $get('visible_to_type') == DocumentVisibleToType::USER->value
                                        )
                                    )
                                    ->searchable()
                                    ->getSearchResultsUsing(
                                        fn (string $search): array => User::where('name', 'like', "%{$search}%")
                                            ->limit(30)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->preload()
                                    ->options(
                                        fn (): array => User::limit(10)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name),

                                Forms\Components\Select::make('visible_to_group')
                                    ->label(__('Group'))
                                    ->live()
                                    ->visible(
                                        fn (callable $get) => in_array(
                                            $get('visible_to_type'),
                                            [
                                                DocumentVisibleToType::GROUP,
                                                DocumentVisibleToType::GROUP->value,
                                                strval(DocumentVisibleToType::GROUP->value),
                                            ]
                                        )
                                    )
                                    ->required(
                                        fn (callable $get) => boolval(
                                            $get('visible_to_type') == DocumentVisibleToType::GROUP->value
                                        )
                                    )
                                    ->searchable()
                                    ->getSearchResultsUsing(
                                        fn (string $search): array => Group::where('name', 'like', "%{$search}%")
                                            ->limit(30)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->preload()
                                    ->options(
                                        fn (): array => Group::limit(10)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->getOptionLabelUsing(fn ($value): ?string => Group::find($value)?->name),
                            ])
                            ->disabled(fn (?Model $record): bool => ($record?->status === DocumentStatus::PUBLISHED))
                            ->hidden(fn (?Model $record) => !static::allowed(['manage'], $record)),

                        Forms\Components\Section::make('Associações')
                            ->hidden(
                                fn (?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->schema([
                                // Forms\Components\Select::make('shop_brand_id')
                                //     ->relationship('brand', 'name')
                                //     ->searchable()
                                //     ->hiddenOn(ProductsRelationManager::class),

                                Forms\Components\Select::make('document_category_id')
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
                            ])
                            ->disabled(fn (?Model $record): bool => ($record?->status === DocumentStatus::PUBLISHED)),

                        Forms\Components\Section::make('Controle')
                            ->hidden(
                                fn (?Model $record) => !static::allowed(['manage'], $record),
                            )
                            ->schema([
                                Forms\Components\Toggle::make('enable_edit_view_status')
                                    ->label('Alterar controle de visualização')
                                    // ->helperText('O documento só poder ser visto se o status for "publicado"')
                                    ->live()
                                    // ->disabled(fn(?Model $record) => !static::allowed(['manage'], $record))
                                    ->hidden(function (?Model $record, string $operation, $state, Forms\Set $set) {
                                        if (
                                            !in_array($operation, ['edit'])
                                            || !static::allowed(['manage'], $record)
                                            || ($record?->status !== DocumentStatus::PUBLISHED)
                                        ) {
                                            return true;
                                        }

                                        return false;
                                    })
                                    ->default(false)
                                    ->dehydrated(false),

                                Forms\Components\Group::make()
                                    ->dehydrated(
                                        fn (?Model $record, callable $get): bool => ($record?->status !== DocumentStatus::PUBLISHED)
                                        || ($record?->status !== DocumentStatus::PUBLISHED) && boolval($get('enable_edit_view_status'))
                                    )
                                    ->disabled(
                                        fn (?Model $record, callable $get): bool => (
                                            $record?->status === DocumentStatus::PUBLISHED &&
                                            !boolval($get('enable_edit_view_status'))
                                        )
                                    )
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->options(
                                                collect(DocumentStatus::cases())
                                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum?->label()])
                                                    ->toArray()
                                            )
                                            ->default(DocumentStatus::DRAFT->value)
                                            ->required()
                                            ->live()
                                            ->preload()
                                            ->native(false),

                                        Forms\Components\Toggle::make('public')
                                            ->label('Disponível para visualização?')
                                            ->live()
                                            ->helperText('Se esse item poderá ser visualizado')
                                            ->default(false)
                                            ->disabled(
                                                fn (?Model $record, callable $get): bool => (
                                                    $record?->status === DocumentStatus::PUBLISHED
                                                ) && !boolval(
                                                    $get('enable_edit_view_status')
                                                )
                                            )
                                            ->hidden(
                                                fn (?Model $record) => !static::allowed(['manage'], $record),
                                            ),

                                        Forms\Components\DatePicker::make('release_date')
                                            ->label('Disponível a partir de')
                                            ->helperText('Data a partir da qual (quando publicado) poderá ser visualizado')
                                            ->default(now())
                                            // ->disabled(fn(?Model $record) => !static::allowed(['manage'], $record))
                                            ->required(fn (callable $get) => (bool) $get('public'))
                                            ->disabled(
                                                fn (?Model $record, callable $get): bool => (
                                                    $record?->status === DocumentStatus::PUBLISHED
                                                ) && !boolval(
                                                    $get('enable_edit_view_status')
                                                )
                                            )
                                            ->hidden(
                                                fn (?Model $record, callable $get) => !$get('public') || !static::allowed(
                                                    ['manage'],
                                                    $record
                                                ),
                                            ),

                                        Forms\Components\DatePicker::make('available_until')
                                            ->label('Disponível até')
                                            ->helperText('Data até a qual o item poderá ser visualizado')
                                            // ->disabled(fn(?Model $record) => !static::allowed(['manage'], $record))
                                            ->hidden(
                                                fn (?Model $record, callable $get) => !$get('public') || !static::allowed(
                                                    ['manage'],
                                                    $record
                                                ),
                                            ),
                                    ]),
                            ])
                            ->hidden(
                                fn (?Model $record) => !static::allowed(['manage'], $record),
                            ),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('documents.id', 'DESC')
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
                    ->formatStateUsing(fn (?Model $record) => $record?->status?->label())
                    ->toggleable(
                        isToggledHiddenByDefault: false,
                    ),

                Tables\Columns\TextColumn::make('visibleType')
                    ->label(
                        static::getTableAttributeLabel('visibleType')
                    )
                    ->sortable()
                    ->formatStateUsing(fn (?Model $record) => $record?->visibleType?->label())
                    ->toggleable(
                        isToggledHiddenByDefault: false,
                    ),

                Tables\Columns\TextColumn::make('visible_to')
                    ->label(
                        static::getTableAttributeLabel('visibleTo')
                    )
                    ->sortable()
                    ->formatStateUsing(fn (?Model $record) => $record?->visibleToValue()?->name)
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
                fn (\Filament\Tables\Actions\Action $action) => $action
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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
                        url: fn (?Model $record) => route('storage_documents.show', $record?->file?->path),
                        shouldOpenInNewTab: true,
                    )
                    ->icon('feathericon-external-link')
                    ->hidden(
                        fn (?Model $record) => !$record?->file
                        || !(
                            ($record?->status === DocumentStatus::PUBLISHED)
                            || static::allowed(['manage'], $record)
                        ),
                    ),
                Tables\Actions\Action::make('download_file')
                    ->label('Baixar anexo')
                    ->url(
                        url: fn (?Model $record) => route(
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
                        fn (?Model $record) => !$record?->file,
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('title')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('slug')
                    ->label('UID')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('status')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('release_date')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('available_until')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('storage_file_id')
                    ->url(
                        url: fn (?Model $record) => $record?->file?->url,
                        shouldOpenInNewTab: true,
                    )
                    ->label('Documento')
                    ->formatStateUsing(fn () => 'Abrir documento')
                    ->icon('feathericon-external-link')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('created_by')
                    ->label('Cadastrado por')
                    ->formatStateUsing(fn (?Model $record) => $record?->creator?->name)
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('public')
                    ->formatStateUsing(fn (?Model $record) => static::userCanManage() && $record?->public ? 'Sim' : 'Não')
                    ->inlineLabel(),

                Infolists\Components\TextEntry::make('category.name')
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
                    ->hidden(fn (?Model $record) => !static::allowed(['manage'], $record))
                    ->columnSpanFull(),

                Section::make('Nota')
                    ->description(
                        fn (?Model $record) => static::allowed(['manage'], $record)
                        ? 'Nota que será visível ao(s) colaborador(es)' : ''
                    )
                    ->schema([
                        Infolists\Components\TextEntry::make('public_note')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed(false)
                    ->id('infolist_public_note')
                    ->columnSpanFull(),
            ]);
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

    public static function userCanManage(): bool
    {
        return auth()?->user()?->can([
            'document::viewAny',
            'document::manage',
        ]) ?? false;
    }

    protected function authorizeAccess(): void
    {
        abort_unless(
            Filament::auth()?->user()?->canAny([
                'document_category::view',
                'document_category::list',
                'document_category::viewAny',
            ]),
            403
        );
    }

    public static function getEloquentQuery(): Builder
    {
        /**
         * @var Builder  $query
         */
        $query = parent::getEloquentQuery()
            ->with([
                'file' => fn ($query) => $query,
            ]);

        if (static::userCanManage()) {
            return $query;
        }

        $query = $query->whereNotNull('visible_to');

        $currentUser = auth()->user();

        $onlyTo = filter_var(request()->query('onlyTo'), FILTER_VALIDATE_INT);

        $onlyType = request()->query('onlyType');

        $onlyType = DocumentVisibleToType::tryByValue($onlyType) ?: DocumentVisibleToType::USER;

        /*
        if (!$onlyType || ($onlyType === DocumentVisibleToType::USER)) {
            $query = $query
                ->where('visible_to_type', DocumentVisibleToType::USER)
                ->where('visible_to', $currentUser?->id);
        }

        if ($onlyType && ($onlyType === DocumentVisibleToType::EVERYONE)) {
            $query = $query->where('visible_to_type', DocumentVisibleToType::EVERYONE)
                ->where('visible_to', DocumentVisibleToType::EVERYONE?->value);
        }

        if ($onlyType && ($onlyType === DocumentVisibleToType::GROUP)) {
            $groups = $onlyTo ?: $currentUser?->groups()?->select('groups.id')?->pluck('id')?->toArray();
            $query = $query->where('visible_to_type', DocumentVisibleToType::GROUP)
                ->whereIn('visible_to', $groups);
        }
        */

        // Backup query
        $query = $query->where(function ($query) use ($currentUser) {
            $groups = $currentUser?->groups()?->select('groups.id')?->pluck('id')?->toArray();

            return $query->where(
                fn ($q1) => $q1->where('visible_to_type', DocumentVisibleToType::GROUP)
                    ->whereIn('visible_to', $groups)
            )
                ->orWhere(
                    fn ($q2) => $q2->where('visible_to_type', DocumentVisibleToType::USER)
                        ->where('visible_to', $currentUser?->id)
                );
        });

        return $query;
    }

    /**
     * Usado em Pages\ManageDocuments e Pages\CreateDocument
     *
     * @param array $data
     *
     * @return array
     */
    public static function mutateDataStorageFile(array $data): array
    {
        $currentUserId = $data['created_by'] ?? null;

        $storageFile = static::storeDocumentFile(
            data: $data,
            uploadedBy: $currentUserId,
            fileIsRequired: false,
        );

        unset($data['document_file']);
        $data['storage_file_id'] = $storageFile?->id;

        return $data;
    }

    public static function storeDocumentFile(
        array $data,
        null|string|int $uploadedBy = null,
        bool $fileIsRequired = false,
        ?string $referenceClass = null,
    ): ?StorageFile {
        $documentFile = array_filter(Arr::wrap($data['document_file'] ?? []));

        if (!$documentFile && !$fileIsRequired) {
            return null;
        }

        if (!$documentFile) {
            throw new \Exception(__('File is required'), 1);
        }

        if (!is_array($documentFile)) {
            throw new \Exception(__('Invalid file'), 1);
        }

        $diskName = $documentFile['diskName'] ?? DocumentResource::getDocumentDisk();
        $fileName = $documentFile['fileName'] ?? null;
        $fileSize = $documentFile['fileSize'] ?? null;
        $clientOriginalName = $documentFile['clientOriginalName'] ?? null;
        $size = $documentFile['size'] ?? null;
        $mimeType = $documentFile['mimeType'] ?? null;
        $realPath = $documentFile['realPath'] ?? null;
        $clientOriginalExtension = $documentFile['clientOriginalExtension'] ?? null;
        $fileOriginalName = $documentFile['fileOriginalName'] ?? null;
        $filePath = $documentFile['filePath'] ?? null;
        $fileExtension = $documentFile['fileExtension'] ?? null;
        $fileName = $documentFile['fileName'] ?? null;
        $newName = $documentFile['newName'] ?? null;

        $storage = Storage::disk($diskName);

        if (!$storage->exists($filePath)) {
            return null;
        }

        return StorageFile::create([
            'disk_name' => $diskName,
            'path' => $fileName,
            'extension' => $fileExtension,
            'size_in_kb' => $fileSize,
            'file_name' => $fileName,
            'original_name' => $fileOriginalName,
            'public' => false,
            'uploaded_by' => $uploadedBy ?: null,
            'reference_class' => $referenceClass,
        ]);
    }

    /**
     * Usado em Pages\ManageDocuments e Pages\CreateDocument
     *
     * @param array $data
     *
     * @return array
     */
    public static function mutateDataVisibleTo(array $data): array
    {
        $visibleToType = ($data['visible_to_type'] ?? null);
        $visibleToType = DocumentVisibleToType::tryByValue($visibleToType);

        if (!$visibleToType) {
            unset($data['visible_to_type']);
        }

        if ($visibleToType) {
            $visibleTo = match ($visibleToType) {
                DocumentVisibleToType::EVERYONE => [
                    'visible_to_type' => DocumentVisibleToType::EVERYONE,
                    'visible_to' => DocumentVisibleToType::EVERYONE?->value,
                ],
                DocumentVisibleToType::USER => [
                    'visible_to_type' => DocumentVisibleToType::USER,
                    'visible_to' => $data['visible_to_user'] ?? null,
                ],
                DocumentVisibleToType::GROUP => [
                    'visible_to_type' => DocumentVisibleToType::GROUP,
                    'visible_to' => $data['visible_to_group'] ?? null,
                ],
                default => [],
            };
        }

        unset($data['visible_to_user'], $data['visible_to_group']);

        return array_merge(
            $data,
            $visibleTo ?? [],
        );
    }
}
