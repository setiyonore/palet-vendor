<?php

namespace App\Filament\Armada\Resources;

use App\Filament\Armada\Resources\MasterJenisBarangResource\Pages;
use App\Models\AM\MasterJenisBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterJenisBarangResource extends Resource
{
    protected static ?string $model = MasterJenisBarang::class;

    // Gunakan icon yang relevan, misalnya 'heroicon-o-cube' atau 'heroicon-o-archive-box'
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 9; // Urutan setelah ShipTo (8)
    protected static ?string $modelLabel = 'Jenis Barang';
    protected static ?string $pluralModelLabel = 'Master Jenis Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jenis Barang')
                    ->schema([
                        Forms\Components\TextInput::make('jenis_barang')
                            ->label('Jenis Barang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Contoh: Semen, Pasir, Palet Kayu'),
                    ])->columns(1), // Cukup 1 kolom karena inputnya sedikit
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('jenis_barang')
                    ->label('Jenis Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListMasterJenisBarangs::route('/'),
            'create' => Pages\CreateMasterJenisBarang::route('/create'),
            'edit' => Pages\EditMasterJenisBarang::route('/{record}/edit'),
        ];
    }
}
