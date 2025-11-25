<?php

namespace App\Filament\Armada\Resources;

use App\Filament\Armada\Resources\MasterPlantResource\Pages;
use App\Models\AM\MasterPlant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterPlantResource extends Resource
{
    protected static ?string $model = MasterPlant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 7;
    protected static ?string $modelLabel = 'Plant';
    protected static ?string $pluralModelLabel = 'Master Plant';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Plant')
                    ->schema([
                        // Select Customer
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'nama')
                            ->searchable()
                            // ->preload()
                            ->required(),

                        // Select Kota (City)
                        Forms\Components\Select::make('city_id')
                            ->label('Kota / Lokasi Plant')
                            ->relationship('city', 'city_name') // 
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
                // Menampilkan Nama Customer
                Tables\Columns\TextColumn::make('customer.nama')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // Menampilkan Nama Kota
                Tables\Columns\TextColumn::make('city.city_name')
                    ->label('Kota Plant')
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
                    ->searchable(),
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
            'index' => Pages\ListMasterPlants::route('/'),
            'create' => Pages\CreateMasterPlant::route('/create'),
            'edit' => Pages\EditMasterPlant::route('/{record}/edit'),
        ];
    }
}
