<?php

namespace App\Filament\Resources;

use App\Enums\DefaultRoleEnum;
use App\Enums\ScrapingStatusEnum;
use App\Filament\Resources\SiteScrapingRuleResource\Pages;
use App\Filament\Resources\SiteScrapingRuleResource\RelationManagers;
use App\Models\SiteScrapingRule;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteScrapingRuleResource extends Resource
{
    protected static ?string $model = SiteScrapingRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Site Profile Management';

    protected static ?int $navigationSort = 2;
    
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
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255),

                        TextInput::make('url')
                            ->required()
                            ->url(),

                        Toggle::make('include_subcategories')
                            ->default(false),

                        TextInput::make('subcategory_selector'),

                        ToggleButtons::make('status')
                            ->inline()
                            ->options(ScrapingStatusEnum::class)
                            ->default(ScrapingStatusEnum::DISABLED)
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('Site')
                    ->column('site_id')
            ])
            ->columns([
                TextColumn::make('site.name')
                    ->sortable(),

                TextColumn::make('field')
                    ->searchable(),

                TextColumn::make('selector')
                    ->searchable(),

                TextColumn::make('type')
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

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
            'index' => Pages\ListSiteScrapingRules::route('/'),
            'create' => Pages\CreateSiteScrapingRule::route('/create'),
            'edit' => Pages\EditSiteScrapingRule::route('/{record}/edit'),
        ];
    }
}
