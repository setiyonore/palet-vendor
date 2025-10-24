<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanManualResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanManualResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPeminjamanManual extends ViewRecord
{
    protected static string $resource = PeminjamanManualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Peminjaman Manual')
                    ->schema([
                        TextEntry::make('nopol')->label('No. Polisi'),
                        TextEntry::make('masterVendor.nama_vendor')->label('Nama Vendor'),
                        TextEntry::make('qty')->label('Jumlah Palet'),
                        TextEntry::make('tgl_pinjam')->label('Tanggal & Waktu Peminjaman')->dateTime(),
                        TextEntry::make('gudangPalet.nama_gudang')->label('Plant Asal'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(int $state): string => match ($state) {
                                0 => 'warning',
                                1 => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(int $state): string => match ($state) {
                                0 => 'Belum Dikembalikan',
                                1 => 'Sudah Dikembalikan',
                                default => 'Tidak Diketahui',
                            }),
                        TextEntry::make('importer')->label('Diimpor Oleh'),
                    ])->columns(2),

                Section::make('Detail Tambahan')
                    ->schema([
                        TextEntry::make('kode_expeditur'),
                        TextEntry::make('no_spj'),
                        TextEntry::make('tgl_spj')->dateTime(),
                        TextEntry::make('tgl_pod')->dateTime(),
                        TextEntry::make('keterangan')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
