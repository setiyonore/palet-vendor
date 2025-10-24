<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PaletStokResource\Pages;
use App\Models\PM\PaletStok;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaletStokResource extends Resource implements HasShieldPermissions
{

    protected static ?string $model = PaletStok::class;

    protected static ?string $pluralModelLabel = 'Stok Palet';

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Stok Palet')
                    ->schema([
                        Select::make('gudang_id')
                            ->label('Plant Palet')
                            ->relationship('gudangPalet', 'nama_gudang')
                            ->getOptionLabelFromRecordUsing(fn($record) => "[{$record->kode_gudang}] {$record->nama_gudang}")
                            ->searchable(['kode_gudang', 'nama_gudang'])
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn(string $operation): bool => $operation === 'edit')
                            ->helperText('Pilih plant untuk mencatat stok awal. Plant tidak bisa diubah saat edit.'),

                        TextInput::make('qty_rfi')
                            ->label('Jumlah RFI (Ready For Issue)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('qty_tbr')
                            ->label('Jumlah TBR (To Be Repaired)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('qty_ber')
                            ->label('Jumlah BER (Beyond Economical Repair)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('gudangPalet.nama_gudang')
                    ->label('Nama Plant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qty_rfi')
                    ->label('Stok RFI')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qty_tbr')
                    ->label('Stok TBR')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qty_ber')
                    ->label('Stok BER')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('last_updated')
                //     ->label('Terakhir Diperbarui')
                //     ->dateTime()
                //     ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Bulk actions bisa dinonaktifkan karena tidak relevan untuk stok
                // Tables\Actions\BulkActionGroup::make([
                // Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListPaletStoks::route('/'),
            'create' => Pages\CreatePaletStok::route('/create'),
            'edit' => Pages\EditPaletStok::route('/{record}/edit'),
            // 'view' => Pages\ViewPaletStok::route('/{record}'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
