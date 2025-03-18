<?php

namespace App\Filament\Resources;

use App\Enums\DefaultRoleEnum;
use App\Enums\UserStatusEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        // Sadece SUPER_ADMIN veya ADMIN rolündeki kullanıcılar erişebilir
        return auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN || 
               auth()->user()->role === DefaultRoleEnum::ADMIN || 
               auth()->user()->hasRole('admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Sadece SUPER_ADMIN veya ADMIN rolündeki kullanıcılar görebilir
        return auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN || 
               auth()->user()->role === DefaultRoleEnum::ADMIN || 
               auth()->user()->hasRole('admin');
    }

    public static function getNavigationGroup(): ?string
    {
        // Sadece SUPER_ADMIN veya ADMIN rolündeki kullanıcılar için User Management grubunu göster
        if (auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN || 
            auth()->user()->role === DefaultRoleEnum::ADMIN || 
            auth()->user()->hasRole('admin')) {
            return 'User Management';
        }
        
        return null; // Diğer kullanıcılar için grup gösterme
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Grid::make()
                            ->schema([
                                TextInput::make('password')
                                    ->label(__('filament-panels::pages/auth/register.form.password.label'))
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required(fn(Page $livewire) => $livewire instanceof Pages\CreateUser)
                                    ->rule(Password::default())
                                    ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)
                                    ->same('passwordConfirmation')
                                    ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),

                                TextInput::make('passwordConfirmation')
                                    ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required(fn(Page $livewire) => $livewire instanceof Pages\CreateUser)
                                    ->dehydrated(false),
                            ]),

                        Select::make('role')
                            ->options(DefaultRoleEnum::class)
                            ->required(),

                        ToggleButtons::make('status')
                            ->inline()
                            ->options(UserStatusEnum::class)
                            ->default(UserStatusEnum::ACTIVE)
                            ->required(),

                        Select::make('roles')
                            ->label('Additional Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->listWithLineBreaks()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customers_count')
                    ->label('Customers')
                    ->badge()
                    ->counts('customers')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(UserStatusEnum::class),

                Tables\Filters\SelectFilter::make('role')
                    ->options(DefaultRoleEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getNavigationBadge(): ?string
    {
        return self::getEloquentQuery()->count();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RolesRelationManager::class,
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
