<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PaletStokHistoriResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Models\PM\PaletStokHistori;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaletStokHistoriResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PaletStokHistori::class;

    protected static ?string $pluralModelLabel = 'Log Stok Palet';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Transaksi')
                    ->schema([
                        TextEntry::make('tgl_transaksi')->dateTime(),
                        TextEntry::make('gudangPalet.nama_gudang'),
                        TextEntry::make('tipe_transaksi')->badge(),
                        TextEntry::make('keterangan'),
                    ])->columns(2),
                Section::make('Perubahan Stok')
                    ->schema([
                        TextEntry::make('perubahan_rfi')->label('Perubahan RFI'),
                        TextEntry::make('perubahan_tbr')->label('Perubahan TBR'),
                        TextEntry::make('perubahan_ber')->label('Perubahan BER'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tgl_transaksi')
                    ->label('Tgl Transaksi')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('gudangPalet.nama_gudang')
                    ->label('Plant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('performer')
                    ->label('User'),
                TextColumn::make('tipe_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PEMINJAMAN' => 'danger',
                        'PENGEMBALIAN' => 'success',
                        'ADJUSTMENT' => 'warning',
                        'PERBAIKAN' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('perubahan_rfi')->label('RFI'),
                TextColumn::make('perubahan_tbr')->label('TBR'),
                TextColumn::make('perubahan_ber')->label('BER'),
                TextColumn::make('keterangan')
                    ->limit(40)
                    ->tooltip('Lihat selengkapnya')
                    ->searchable(),
            ])
            ->defaultSort('tgl_transaksi', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaletStokHistoris::route('/'),
            'view' => Pages\ViewPaletStokHistori::route('/{record}'),
        ];
    }
}
