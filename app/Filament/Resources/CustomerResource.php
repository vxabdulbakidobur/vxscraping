<?php

namespace App\Filament\Resources;

use App\Enums\CustomerStatusEnum;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\DefaultRoleEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

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

    public static function shouldRegisterNavigation(): bool
    {
        // Sadece SUPER_ADMIN veya ADMIN rolündeki kullanıcılar görebilir
        return auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN || 
               auth()->user()->role === DefaultRoleEnum::ADMIN || 
               auth()->user()->hasRole('admin');
    }

    public static function canAccess(): bool
    {
        // Sadece SUPER_ADMIN veya ADMIN rolündeki kullanıcılar erişebilir
        return auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN || 
               auth()->user()->role === DefaultRoleEnum::ADMIN || 
               auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Müşteri Bilgileri')
                    ->schema([
                        Select::make('user_id')
                            ->label('Account')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(auth()->user()->isSuperAdmin())
                            ->required(),

                        Grid::make()
                            ->schema([
                                TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        ToggleButtons::make('status')
                            ->inline()
                            ->options(CustomerStatusEnum::class)
                            ->default(CustomerStatusEnum::PENDING)
                            ->required(),
                    ]),
                
                Section::make('Paket Bilgileri')
                    ->schema([
                        Select::make('package_id')
                            ->label('Aktif Paket')
                            ->relationship('package', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Müşterinin aktif olarak kullandığı paket'),
                            
                        DateTimePicker::make('expire_date')
                            ->label('Bitiş Tarihi')
                            ->helperText('Paketin geçerlilik süresi')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Account'),

                TextColumn::make('full_name')
                    ->label('Customer')
                    ->searchable(['first_name', 'last_name', 'email']),

                TextColumn::make('email')
                    ->searchable(),
                
                TextColumn::make('package.name')
                    ->label('Aktif Paket')
                    ->sortable(),
                
                TextColumn::make('expire_date')
                    ->label('Paket Bitiş Tarihi')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('sites_count')
                    ->counts('sites')
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CustomerStatusEnum::class)
                    ->multiple(),
                
                SelectFilter::make('package_id')
                    ->label('Paket')
                    ->relationship('package', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(auth()->user()->isSuperAdmin()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin kullanıcılar tüm müşterileri görebilir
        if (auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN || 
            auth()->user()->role === DefaultRoleEnum::ADMIN || 
            auth()->user()->hasRole('admin')) {
            return parent::getEloquentQuery();
        }
        
        // Normal kullanıcılar sadece kendi müşterilerini görebilir
        return parent::getEloquentQuery()->owner();
    }

    public static function getNavigationBadge(): ?string
    {
        return self::getEloquentQuery()->count();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PackagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
