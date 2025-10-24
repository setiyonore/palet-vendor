<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\GudangPaletResource\Pages;
use App\Models\PM\GudangPalet;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GudangPaletResource extends Resource
{
    protected static ?string $model = GudangPalet::class;

    protected static ?string $modelLabel = 'Plant Palet';
    protected static ?string $pluralModelLabel = 'Plant Palet';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('kode_gudang')
                            ->label('Kode Plant')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('nama_gudang')
                            ->label('Nama Plant')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('lokasi')
                            ->label('Alamat / Lokasi Plant')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_gudang')
                    ->label('Kode Plant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_gudang')
                    ->label('Nama Plant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lokasi')
                    ->limit(50)
                    ->tooltip('Lihat selengkapnya')
            ])
            ->filters([
                // Filter bisa ditambahkan di sini jika perlu
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            // Relasi bisa didefinisikan di sini
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGudangPalets::route('/'),
            'create' => Pages\CreateGudangPalet::route('/create'),
            'edit' => Pages\EditGudangPalet::route('/{record}/edit'),
            // 'view' => Pages\ViewGudangPalet::route('/{record}'),
        ];
    }
}
