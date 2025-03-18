<?php

namespace App\Filament\Resources;

use App\Enums\DefaultRoleEnum;
use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Package Management';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Package Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Package Specifications')
                    ->schema([
                        Forms\Components\TextInput::make('site_count')
                            ->label('Number of Sites')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),

                        Forms\Components\TextInput::make('url_count')
                            ->label('Number of URLs')
                            ->required()
                            ->numeric()
                            ->default(10)
                            ->minValue(1),

                        Forms\Components\Select::make('scan_frequency')
                            ->label('Scan Frequency (MultiThread)')
                            ->options([
                                'hourly' => 'Hourly',
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->required(),

                        Forms\Components\Select::make('processor_count')
                            ->label('Number of Processors')
                            ->options([
                                1 => '1 Processor',
                                2 => '2 Processors',
                                4 => '4 Processors',
                                8 => '8 Processors',
                            ])
                            ->required()
                            ->default(1),

                        Forms\Components\Select::make('memory')
                            ->label('Memory')
                            ->options([
                                '2Gb' => '2 GB',
                                '4Gb' => '4 GB',
                                '6Gb' => '6 GB',
                                '8Gb' => '8 GB',
                                '16Gb' => '16 GB',
                                '32Gb' => '32 GB',
                            ])
                            ->required()
                            ->default('2Gb'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('site_count')
                    ->label('Sites')
                    ->sortable(),

                Tables\Columns\TextColumn::make('url_count')
                    ->label('URLs')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scan_frequency')
                    ->label('Frequency')
                    ->sortable(),

                Tables\Columns\TextColumn::make('processor_count')
                    ->label('CPUs')
                    ->sortable(),

                Tables\Columns\TextColumn::make('memory')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('currentCustomers_count')
                    ->label('Active Users')
                    ->counts('currentCustomers')
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
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\CustomersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
