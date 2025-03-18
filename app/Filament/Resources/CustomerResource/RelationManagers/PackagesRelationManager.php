<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackagesRelationManager extends RelationManager
{
    protected static string $relationship = 'packages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('package_id')
                    ->relationship('package', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DateTimePicker::make('start_date')
                    ->default(now())
                    ->required(),

                Forms\Components\DateTimePicker::make('expire_date')
                    ->default(now()->addYear())
                    ->required(),

                Forms\Components\TextInput::make('price_paid')
                    ->numeric()
                    ->prefix('₺')
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Forms\Components\Textarea::make('notes')
                    ->label('Notlar')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Paket Adı')
                    ->searchable(),

                Tables\Columns\TextColumn::make('site_count')
                    ->label('Site Sayısı')
                    ->sortable(),

                Tables\Columns\TextColumn::make('url_count')
                    ->label('URL Sayısı')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Başlangıç Tarihi')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expire_date')
                    ->label('Bitiş Tarihi')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_paid')
                    ->label('Ödenen Ücret')
                    ->money('TRY')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Durum')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Pasif',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('expire_date')
                            ->default(now()->addYear())
                            ->required(),
                        Forms\Components\TextInput::make('price_paid')
                            ->numeric()
                            ->prefix('₺')
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notlar')
                            ->maxLength(65535),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        // Eğer bu paket aktifse, müşterinin aktif paketini güncelle
                        if ($data['is_active']) {
                            $this->ownerRecord->update([
                                'package_id' => $data['package_id'],
                                'expire_date' => $data['expire_date'],
                            ]);
                        }
                        
                        return $data;
                    }),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
} 