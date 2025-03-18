<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Enums\DefaultRoleEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255)
                            ->unique(Role::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Set as default role')
                            ->helperText('Default role will be automatically assigned to new users'),

                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->options([
                                'view_users' => 'View Users',
                                'create_users' => 'Create Users',
                                'edit_users' => 'Edit Users',
                                'delete_users' => 'Delete Users',
                                'view_roles' => 'View Roles',
                                'create_roles' => 'Create Roles',
                                'edit_roles' => 'Edit Roles',
                                'delete_roles' => 'Delete Roles',
                                'view_customers' => 'View Customers',
                                'create_customers' => 'Create Customers',
                                'edit_customers' => 'Edit Customers',
                                'delete_customers' => 'Delete Customers',
                                'view_sites' => 'View Sites',
                                'create_sites' => 'Create Sites',
                                'edit_sites' => 'Edit Sites',
                                'delete_sites' => 'Delete Sites',
                            ])
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),
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
                //
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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

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
}
