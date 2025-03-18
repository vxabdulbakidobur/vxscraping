<?php

namespace App\Filament\Resources;

use App\Enums\DefaultRoleEnum;
use App\Enums\DefaultScrapingRuleTypeEnum;
use App\Enums\ScrapingStatusEnum;
use App\Filament\Resources\SiteResource\Pages;
use App\Filament\Resources\SiteResource\RelationManagers;
use App\Models\Site;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Site Profile Management';

    protected static ?string $modelLabel = 'Site Profile';

    public static function shouldRegisterNavigation(): bool
    {
        // Admin kullanıcılar her zaman görebilir
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
            ->columns(1)
            ->schema([
                Section::make('General Information')
                    ->collapsible()
                    ->schema([
                        Select::make('user_id')
                            ->label('Account')
                            ->relationship('user', 'name')
                            ->hintIcon('heroicon-s-lock-closed')
                            ->hintIconTooltip('Only Super-Admin can be see and changed')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('customer_id', null);
                            })
                            ->visible(auth()->user()->isSuperAdmin())
                            ->required(),

                        Select::make('customer_id')
                            ->relationship('customer', 'first_name', function (Builder $query, Get $get) {
                                $userId = auth()->user()->id;

                                if (auth()->user()->isSuperAdmin() && $get('user_id')) {
                                    $userId = $get('user_id');
                                }

                                $query->where('user_id', $userId);
                            })
                            ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->full_name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->hintActions([
                                Action::make('new_customer')
                                    ->icon('heroicon-o-plus')
                                    ->url(fn() => CustomerResource::getUrl('create'), true)
                            ])
                            ->required(),

                        TextInput::make('name')
                            ->label('Site Name')
                            ->maxLength(255)
                            ->required(),

                        ToggleButtons::make('status')
                            ->inline()
                            ->options(ScrapingStatusEnum::class)
                            ->default(ScrapingStatusEnum::ENABLED)
                            ->required(),
                    ]),

                Grid::make(),

                Repeater::make('siteCategories')
                    ->relationship()
                    ->columns(1)
                    ->defaultItems(1)
                    ->minItems(1)
                    ->collapsible()
                    ->collapsed(fn(Page $livewire) => $livewire instanceof Pages\EditSite)
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Category Name')
                                    ->columnSpan(1)
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->required(),

                                TextInput::make('url')
                                    ->label('Category URL')
                                    ->url()
                                    ->columnSpan(3)
                                    ->required(),

                                Grid::make(1)
                                    ->columnSpan(1)
                                    ->schema([
                                        ToggleButtons::make('status')
                                            ->inline()
                                            ->options(ScrapingStatusEnum::class)
                                            ->default(ScrapingStatusEnum::ENABLED)
                                            ->required(),
                                    ])
                            ]),


                    ]),

                Grid::make(),

                Section::make('Global Selectors')
                    ->collapsible()
                    ->collapsed(fn(Page $livewire) => $livewire instanceof Pages\EditSite)
                    ->schema([
                        TextInput::make('pagination_item_selector')
                            ->label('Pagination next item selector'),

                        TextInput::make('product_item_selector')
                            ->required(),

                        Grid::make()
                            ->schema([
                                Toggle::make('include_subcategories')
                                    ->inline(false)
                                    ->default(false)
                                    ->columnSpan(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('subcategory_selector', null);
                                    }),

                                TextInput::make('subcategory_selector')
                                    ->label('Subcategory item selector')
                                    ->columnSpan(3)
                                    ->visible(fn(Get $get) => (bool)$get('include_subcategories'))
                                    ->required(fn(Get $get) => (bool)$get('include_subcategories')),
                            ]),
                    ]),

                Grid::make(),

                Repeater::make('siteScrapingRules')
                    ->label('Scraping rules for the product page')
                    ->relationship()
                    ->columns(1)
                    ->defaultItems(1)
                    ->minItems(2)
                    ->collapsible()
                    ->collapsed(fn(Page $livewire) => $livewire instanceof Pages\EditSite)
                    ->itemLabel(fn(array $state): ?string => $state['field'] ?? null)
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                Grid::make(1)
                                    ->extraAttributes([
                                        'class' => 'hidden'
                                    ])
                                    ->schema([
                                        TextInput::make('field')
                                            ->label('Field Name')
                                            ->columnSpan(1)
                                            ->live(onBlur: true)
                                            ->maxLength(255)
                                            ->required(),
                                    ]),

                                TextInput::make('selector')
                                    ->columnSpan(3)
                                    ->label('Selector')
                                    ->required(),

                                Select::make('type')
                                    ->columnSpan(1)
                                    ->options(DefaultScrapingRuleTypeEnum::class)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('field', $state);
                                    })
                                    ->required(),

                                ToggleButtons::make('status')
                                    ->inline()
                                    ->options(ScrapingStatusEnum::class)
                                    ->default(ScrapingStatusEnum::ENABLED)
                                    ->required(),
                            ]),
                    ]),

                Grid::make(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Account')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable(['first_name', 'last_name', 'email']),

                TextColumn::make('name')
                    ->label('Site name')
                    ->searchable(),

                TextColumn::make('url')
                    ->label('Site URL')
                    ->searchable(),

                TextColumn::make('site_categories_count')
                    ->label('Categories')
                    ->counts('siteCategories')
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('site_scraping_rules_count')
                    ->label('Rules')
                    ->counts('siteScrapingRules')
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('queue_status')
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
                Tables\Filters\SelectFilter::make('status')
                    ->options(ScrapingStatusEnum::class),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Account')
                    ->relationship('user', 'name')
                    ->multiple()
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
        return parent::getEloquentQuery()->owner();
    }

    public static function getNavigationBadge(): ?string
    {
        return self::getEloquentQuery()->count();
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
