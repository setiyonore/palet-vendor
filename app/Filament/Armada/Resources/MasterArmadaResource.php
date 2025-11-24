<?php

namespace App\Filament\Armada\Resources;

use App\Filament\Armada\Resources\MasterArmadaResource\Pages;
use App\Filament\Armada\Resources\MasterArmadaResource\RelationManagers;
use App\Models\AM\MasterArmada;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterArmadaResource extends Resource
{
    protected static ?string $model = MasterArmada::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Armada';
    protected static ?string $pluralModelLabel = 'Master Armada';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Armada')
                    ->schema([
                        // Select Jenis Truk
                        Forms\Components\Select::make('tipe_armada_id')
                            ->label('Jenis Truk')
                            ->relationship('tipeArmada', 'nama_tipe')
                            ->required()
                            ->searchable()
                            ->preload(),

                        // Input Nomor Pintu
                        Forms\Components\TextInput::make('nomor_pintu')
                            ->label('Nomor Pintu')
                            ->required()
                            ->maxLength(255),

                        // Input Nomor Polisi
                        Forms\Components\TextInput::make('nomor_polisi')
                            ->label('Nomor Polisi')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        // Select Driver
                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->relationship('driver', 'nama')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipeArmada.nama_tipe')
                    ->label('Jenis Truk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_pintu')
                    ->label('No Pintu')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nomor_polisi')
                    ->label('No Polisi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('driver.nama')
                    ->label('Driver')
                    ->searchable(),
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
            'index' => Pages\ListMasterArmadas::route('/'),
            'create' => Pages\CreateMasterArmada::route('/create'),
            'edit' => Pages\EditMasterArmada::route('/{record}/edit'),
        ];
    }
}
