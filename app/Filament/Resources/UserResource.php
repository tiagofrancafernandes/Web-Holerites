<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Traits\ModelLabel;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserResource extends \App\Filament\Resources\Extended\ExtendedResourceBase
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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Password'))
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(static::getFormAttributeLabel('name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('email')
                                    ->label(static::getFormAttributeLabel('email'))
                                    ->email()
                                    ->required()
                                    ->disabled(fn(?Model $record): bool => boolval($record?->is_canonical))
                                    ->dehydrated(fn($state, ?Model $record): bool => !$record || !($record?->is_canonical))
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\DateTimePicker::make('email_verified_at')
                                    ->label(static::getFormAttributeLabel('email_verified_at'))
                                    ->placeholder(static::getFormAttributeLabel('email_verified_at'))
                                    ->displayFormat('j M Y H:i')
                                    ->native(false)
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),

                        Forms\Components\Section::make(__('Password'))
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label(static::getFormAttributeLabel('password'))
                                    ->password(fn(callable $get) => !$get('showPassword'))
                                    ->rule(Password::default())
                                    ->autocomplete('new-password')
                                    ->dehydrated(fn($state): bool => filled($state))
                                    ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                                    ->live(debounce: 500)
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->same('passwordConfirmation')
                                    ->helperText(
                                        fn(string $operation) => ($operation === 'create')
                                        ? null : 'Deixe em branco se não quiser alterar'
                                    )
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('passwordConfirmation')
                                    ->label(static::getFormAttributeLabel('password_confirmation'))
                                    ->password(fn(callable $get) => !$get('showPassword'))
                                    ->required()
                                    ->live(debounce: 500)
                                    ->visible(fn(\Filament\Forms\Get $get): bool => filled($get('password')))
                                    ->dehydrated(false)
                                    ->columnSpan(2),

                                Forms\Components\Toggle::make('showPassword')
                                    ->label(static::getFormAttributeLabel('show_password'))
                                    ->onIcon('heroicon-s-eye')
                                    ->offIcon('heroicon-s-eye-slash')
                                    ->dehydrated(false)
                                    ->live()
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Roles')
                            ->heading(__('models.User.form.section_roles_heading'))
                            ->schema([
                                Forms\Components\Select::make('main_web_role')
                                    ->label(__('models.User.form.main_web_role'))
                                    // ->relationship('mainWebRole', 'name')
                                    ->options(
                                        Role::where('guard_name', 'web')
                                            ->select(['name', 'id'])
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->columnSpan(2),

                                Forms\Components\Select::make('main_api_role')
                                    ->label(__('models.User.form.main_api_role'))
                                    // ->relationship('mainApiRole', 'name')
                                    ->options(
                                        Role::where('guard_name', 'api')
                                            ->select(['name', 'id'])
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->columnSpan(2),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label(static::getFormAttributeLabel('status'))
                                    ->required()
                                    ->native(false)
                                    ->default(1)
                                    ->options(
                                        collect(UserStatus::cases())
                                            ->mapWithKeys(fn($e) => [$e->value => $e->label()])
                                            ->toArray()
                                    ),
                            ]),

                        Forms\Components\Section::make('extra_info')
                            ->heading(__('models.User.form.section_extra_info_heading'))
                            ->schema([
                                Forms\Components\Select::make('language')
                                    ->label(static::getFormAttributeLabel('language'))
                                    ->searchable()
                                    ->options([
                                        'en' => 'en',
                                        'pt_BR' => 'pt_BR',
                                    ])
                                    ->default(config('app.locale'))
                                    ->native(false),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
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
                    ->formatStateUsing(function (?Model $record) {
                        $enum = $record?->status;
                        $active = boolval($enum?->value);
                        $text = $enum?->label() ?? '';
                        $color = $active ? 'success' : 'danger';

                        return html()->element('span')
                            ->html($text)
                            ->attributes([
                                'class' => 'font-semibold group-hover/link:underline group-focus-visible/link:underline text-sm text-custom-600 dark:text-custom-400',
                                'style' => "--c-400:var(--{$color}-400);--c-600:var(--{$color}-600);",
                            ]);
                    })
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
                        function (Model $record): bool {
                            return !auth()->user()->cachedCanAny([
                                'user::edit',
                                'user::editAny',
                                'user::forceDelete',
                                'user::forceDeleteAny',
                                'user::update',
                                'user::updateAny',
                            ]);
                        }
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label(static::getActionLabel('delete'))
                    ->hidden(
                        function (Model $record): bool {
                            if ($record?->deleted_at) {
                                return true;
                            }

                            if ($record?->is_canonical) {
                                return true;
                            }

                            if (
                                !auth()->user()->cachedCanAny([
                                    // 'user::create',
                                    'user::delete',
                                    'user::deleteAny',
                                    'user::edit',
                                    'user::editAny',
                                    'user::forceDelete',
                                    'user::forceDeleteAny',
                                    // 'user::list',
                                    // 'user::listAll',
                                    // 'user::reorder',
                                    // 'user::reorderAny',
                                    // 'user::restore',
                                    // 'user::restoreAny',
                                    'user::update',
                                    'user::updateAny',
                                ])
                            ) {
                                return true;
                            }

                            return $record?->id === auth()->user()->id;
                        }
                    ),

                Tables\Actions\RestoreAction::make()
                    ->label(static::getActionLabel('restore'))
                    ->hidden(
                        function (Model $record): bool {
                            if (!$record?->deleted_at) {
                                return true;
                            }

                            if ($record?->is_canonical) {
                                return false;
                            }

                            return !auth()->user()->cachedCanAny([
                                    // 'user::create',
                                    'user::delete',
                                    'user::deleteAny',
                                    'user::edit',
                                    'user::editAny',
                                    'user::forceDelete',
                                    'user::forceDeleteAny',
                                    // 'user::list',
                                    // 'user::listAll',
                                    // 'user::reorder',
                                    // 'user::reorderAny',
                                    'user::restore',
                                    'user::restoreAny',
                                    'user::update',
                                    'user::updateAny',
                                ]);
                        }
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
            RelationManagers\GroupsRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
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
