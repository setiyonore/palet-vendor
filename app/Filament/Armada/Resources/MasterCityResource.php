<?php

namespace App\Filament\Armada\Resources;

use App\Filament\Armada\Resources\MasterCityResource\Pages;
use App\Filament\Armada\Resources\MasterCityResource\RelationManagers;
use App\Models\AM\MasterCity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterCityResource extends Resource
{
    protected static ?string $model = MasterCity::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Kota';
    protected static ?string $pluralModelLabel = 'Master Kota';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('city_name')
                    ->label('Nama Kota / Kabupaten')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('province')
                    ->label('Provinsi')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('nation')
                    ->label('Negara')
                    ->default('Indonesia')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('city_name')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('province')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nation')
                    ->label('Negara')
                    ->sortable(),
            ])
            ->filters([
                // Filter berdasarkan Provinsi jika perlu
                Tables\Filters\SelectFilter::make('province')
                    ->label('Provinsi')
                    ->options(fn() => MasterCity::distinct()->pluck('province', 'province')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterCities::route('/'),
            'create' => Pages\CreateMasterCity::route('/create'),
            'edit' => Pages\EditMasterCity::route('/{record}/edit'),
        ];
    }
}
