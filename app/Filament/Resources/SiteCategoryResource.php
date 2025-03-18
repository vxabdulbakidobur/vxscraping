<?php

namespace App\Filament\Resources;

use App\Enums\DefaultRoleEnum;
use App\Enums\ScrapingStatusEnum;
use App\Filament\Resources\SiteCategoryResource\Pages;
use App\Filament\Resources\SiteCategoryResource\RelationManagers;
use App\Models\SiteCategory;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteCategoryResource extends Resource
{
    protected static ?string $model = SiteCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Site Profile Management';

    protected static ?int $navigationSort = 1;
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public static function canAccess(): bool
    {
        // Admin kullanıcılar her zaman erişebilir
        if (auth()->user()->isAdmin()) {
            return true;
        }
        
        // Normal kullanıcılar için aktif paket kontrolü yap
        if (auth()->check()) {
            $customer = \App\Models\Customer::where('user_id', auth()->id())->first();
            return $customer && $customer->hasActivePackage();
        }
        
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('site_id')
                            ->relationship('site', 'name')
                            ->required(),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('url')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('include_subcategories')
                            ->default(false),

                        TextInput::make('subcategory_selector')
                            ->maxLength(255),

                        ToggleButtons::make('status')
                            ->inline()
                            ->options(ScrapingStatusEnum::class)
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('url')
                    ->searchable(),

                Tables\Columns\IconColumn::make('include_subcategories')
                    ->boolean(),

                TextColumn::make('subcategory_selector')
                    ->searchable(),

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
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
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
            'index' => Pages\ListSiteCategories::route('/'),
            'create' => Pages\CreateSiteCategory::route('/create'),
            'edit' => Pages\EditSiteCategory::route('/{record}/edit'),
        ];
    }
}
