<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PaletStokHistoriResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Models\PM\PaletStokHistori;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfoSection;
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
    protected static ?string $navigationGroup = 'Inventori';
    protected static ?int $navigationSort = 3;

    /* ======================
     * NAV & ACCESS GUARDS
     * ====================== */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_palet::stok::histori') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_palet::stok::histori') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // histori tidak dibuat manual
    }

    public static function canEdit($record): bool
    {
        return false; // histori tidak diedit
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_palet::stok::histori') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_palet::stok::histori') ?? false;
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Detail Transaksi')->schema([
                TextEntry::make('tgl_transaksi')->dateTime(),
                TextEntry::make('gudangPalet.nama_gudang'),
                TextEntry::make('tipe_transaksi')->badge(),
                TextEntry::make('keterangan'),
            ])->columns(2),
            InfoSection::make('Perubahan Stok')->schema([
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
                TextColumn::make('tgl_transaksi')->label('Tgl Transaksi')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('gudangPalet.nama_gudang')->label('Plant')->searchable()->sortable(),
                TextColumn::make('performer')->label('User'),
                TextColumn::make('tipe_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PEMINJAMAN'   => 'danger',
                        'PENGEMBALIAN' => 'success',
                        'ADJUSTMENT'   => 'warning',
                        'PERBAIKAN'    => 'info',
                        default        => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('perubahan_rfi')->label('RFI'),
                TextColumn::make('perubahan_tbr')->label('TBR'),
                TextColumn::make('perubahan_ber')->label('BER'),
                TextColumn::make('keterangan')->limit(40)->tooltip('Lihat selengkapnya')->searchable(),
            ])
            ->defaultSort('tgl_transaksi', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => auth()->user()?->can('view_palet::stok::histori') ?? false),
                // Hapus/komentari jika tidak mau izinkan delete histori via UI
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->can('delete_palet::stok::histori') ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->can('delete_any_palet::stok::histori') ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaletStokHistoris::route('/'),
            'view'  => Pages\ViewPaletStokHistori::route('/{record}'),
        ];
    }
}
