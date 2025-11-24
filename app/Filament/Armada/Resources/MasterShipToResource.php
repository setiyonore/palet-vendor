<?php

namespace App\Filament\Armada\Resources;

use App\Filament\Armada\Resources\MasterShipToResource\Pages;
use App\Models\AM\MasterShipTo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterShipToResource extends Resource
{
    protected static ?string $model = MasterShipTo::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 8;
    protected static ?string $modelLabel = 'Ship To';
    protected static ?string $pluralModelLabel = 'Master Ship To';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Ship To')
                    ->schema([
                        // Select Customer
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'nama')
                            ->searchable()
                            // ->preload() 
                            ->required(),

                        // Select Kota
                        Forms\Components\Select::make('city_id')
                            ->label('Kota Tujuan')
                            ->relationship('city', 'city_name')
                            ->searchable()
                            // ->preload() 
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.nama')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city.city_name')
                    ->label('Kota Tujuan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city.province')
                    ->label('Provinsi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'nama')
                    ->searchable(), // Filter customer juga bisa dibuat searchable
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterShipTos::route('/'),
            'create' => Pages\CreateMasterShipTo::route('/create'),
            'edit' => Pages\EditMasterShipTo::route('/{record}/edit'),
        ];
    }
}
